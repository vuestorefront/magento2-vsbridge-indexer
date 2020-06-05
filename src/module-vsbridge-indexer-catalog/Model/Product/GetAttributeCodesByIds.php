<?php declare(strict_types=1);

/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Product;

use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\LoadAttributes;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Class GetAttributeCodesByIds
 */
class GetAttributeCodesByIds
{
    /**
     * @var LoadAttributes
     */
    private $loadAttributes;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * GetAttributeCodesByIds constructor.
     *
     * @param LoadAttributes $loadAttributes
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoadAttributes $loadAttributes,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->loadAttributes = $loadAttributes;
    }

    /**
     * @param string $attributeIds
     *
     * @return array
     */
    public function execute(string $attributeIds): array
    {
        $attributes = explode(',', $attributeIds);
        $attributeCodes = [];

        foreach ($attributes as $attributeId) {
            try {
                $attribute = $this->loadAttributes->getAttributeById((int)$attributeId);
                $attributeCodes[] = $attribute->getAttributeCode();
            } catch (LocalizedException $e) {
                $this->logger->info($e->getMessage());
            }
        }

        return $attributeCodes;
    }
}
