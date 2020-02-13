<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Category;

use Divante\VsbridgeIndexerCatalog\Api\ApplyCategorySlugInterface;
use Divante\VsbridgeIndexerCatalog\Api\CatalogConfigurationInterface;
use Divante\VsbridgeIndexerCatalog\Api\SlugGeneratorInterface;

/**
 * Class ApplyCategorySlug
 */
class ApplyCategorySlug implements ApplyCategorySlugInterface
{

    /**
     * @var CatalogConfigurationInterface
     */
    private $settings;

    /**
     * @var SlugGeneratorInterface
     */
    private $slugGenerator;

    /**
     * ApplySlug constructor.
     *
     * @param SlugGeneratorInterface $slugGenerator
     * @param CatalogConfigurationInterface $configSettings
     */
    public function __construct(
        SlugGeneratorInterface $slugGenerator,
        CatalogConfigurationInterface $configSettings
    ) {
        $this->settings = $configSettings;
        $this->slugGenerator = $slugGenerator;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $category)
    {
        if ($this->settings->useMagentoUrlKeys()) {
            if (!isset($category['url_key'])) {
                $slug = $this->slugGenerator->generate(
                    $category['name'],
                    $category['entity_id']
                );
                $category['url_key'] = $slug;
            }

            $category['slug'] = $category['url_key'];
        } else {
            $text = $category['name'];

            if ($this->settings->useUrlKeyToGenerateSlug() && isset($category['url_key'])) {
                $text = $category['url_key'];
            }

            $slug = $this->slugGenerator->generate($text, $category['entity_id']);
            $category['url_key'] = $slug;
            $category['slug'] = $slug;
        }

        return $category;
    }
}
