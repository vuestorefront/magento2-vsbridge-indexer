<?php
/**
 * @package  Divante\VsbridgeIndexerCore
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Cache;

use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface;

/**
 * Class Processor
 */
class Processor
{
    /**
     * Mapping elastic type to cache tag used by vsf
     * @var array
     */
    private $defaultCacheTags = [
        'category' => 'C',
        'product' => 'P',
    ];

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $cacheTags;

    /**
     * @var CurlFactory
     */
    private $curlFactory;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * Processor constructor.
     *
     * @param CurlFactory $curlFactory
     * @param Settings $configSettings
     * @param EventManager $manager
     * @param LoggerInterface $logger
     */
    public function __construct(
        CurlFactory $curlFactory,
        Settings $configSettings,
        EventManager $manager,
        LoggerInterface $logger
    ) {
        $this->eventManager = $manager;
        $this->curlFactory = $curlFactory;
        $this->logger = $logger;
        $this->settings = $configSettings;
    }

    /**
     * @param int $storeId
     * @param string $dataType
     * @param array $entityIds
     *
     * @return $this
     */
    public function cleanCacheByDocIds($storeId, $dataType, array $entityIds)
    {
        if ($this->settings->clearCache($storeId)) {
            if (!empty($entityIds)) {
                $cacheInvalidateUrl = $this->getCacheInvalidateUrl($storeId, $dataType, $entityIds);

                try {
                    $this->call($storeId, $cacheInvalidateUrl);
                } catch (\Exception $e) {
                    $this->logger->error($e->getMessage());
                }
            } else {
                $cacheTags = $this->getCacheTags();

                if (isset($cacheTags[$dataType])) {
                    $this->cleanCacheByTags($storeId, [$dataType]);
                }
            }
        }

        return $this;
    }

    /**
     * @param int $storeId
     * @param array $tags
     */
    public function cleanCacheByTags($storeId, array $tags)
    {
        $cacheTags = implode(',', $tags);
        $cacheInvalidateUrl = $this->getInvalidateCacheUrl($storeId) . $cacheTags;

        try {
            $this->call($storeId, $cacheInvalidateUrl);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @param string $storeId
     * @param string $uri
     */
    private function call($storeId, $uri)
    {
        $config = $this->settings->getConnectionOptions($storeId);
        /** @var \Magento\Framework\HTTP\Adapter\Curl $curl */
        $curl = $this->curlFactory->create();
        $curl->setConfig($config);
        $curl->write(\Zend_Http_Client::GET, $uri, '1.0');
        $response = $curl->read();

        if ($response !== false && !empty($response)) {
            $httpCode = \Zend_Http_Response::extractCode($response);

            if ($httpCode !== 200) {
                $response = \Zend_Http_Response::extractBody($response);
                $this->logger->error($response);
            }
        } else {
            $this->logger->error('Problem with clearing VSF cache.');
        }
    }

    /**
     * @param int $storeId
     * @param string $type
     * @param array  $ids
     *
     * @return string
     */
    private function getCacheInvalidateUrl($storeId, $type, array $ids)
    {
        $fullUrl = $this->getInvalidateCacheUrl($storeId);
        $params = $this->prepareTagsByDocIds($type, $ids);
        $fullUrl .= $params;

        return $fullUrl;
    }

    /**
     * @return string
     */
    private function getInvalidateCacheUrl($storeId)
    {
        $url = $this->settings->getVsfBaseUrl($storeId);
        $url .= sprintf('invalidate?key=%s&tag=', $this->settings->getInvalidateCacheKey($storeId));

        return $url;
    }

    /**
     * @param string $type
     * @param array $ids
     *
     * @return string
     */
    public function prepareTagsByDocIds($type, array $ids)
    {
        $params = '';
        $cacheTags = $this->getCacheTags();

        if (isset($cacheTags[$type])) {
            $cacheTag = $cacheTags[$type];
            $count = count($ids);

            foreach ($ids as $key => $id) {
                $params .= $cacheTag . $id;

                if ($key !== ($count - 1)) {
                    $params .= ',';
                }
            }
        }

        return $params;
    }

    /**
     * @return array
     */
    public function getCacheTags()
    {
        if (null === $this->cacheTags) {
            $tagsDataObject = new \Magento\Framework\DataObject();
            $tagsDataObject->setData('items', $this->defaultCacheTags);
            $this->eventManager->dispatch(
                'vsf_prepare_cache_tags',
                ['cache_tags' => $tagsDataObject]
            );
            $this->cacheTags = $tagsDataObject->getData('items');
        }

        return $this->cacheTags;
    }
}
