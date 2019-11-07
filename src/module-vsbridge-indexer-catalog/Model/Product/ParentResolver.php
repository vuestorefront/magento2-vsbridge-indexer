<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Marcin Dykas <mdykas@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

declare(strict_types=1);

namespace Divante\VsbridgeIndexerCatalog\Model\Product;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;

/**
 * Class ParentResolver
 */
class ParentResolver
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * ParentResolver constructor.
     *
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductRepositoryInterface $productRepository
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductRepositoryInterface $productRepository,
        FilterBuilder $filterBuilder
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productRepository = $productRepository;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * @param array $parentIds
     * @return array
     */
    public function getParentProductsByIds(array $parentIds)
    {
        $parentSkus = [];

        $this->filterBuilder->setField('entity_id');
        $this->filterBuilder->setValue($parentIds);
        $this->filterBuilder->setConditionType('in');

        $this->searchCriteriaBuilder->addFilters([$this->filterBuilder->create()]);

        $searchResult = $this->productRepository->getList($this->searchCriteriaBuilder->create());

        foreach ($searchResult->getItems() as $item) {
            $parentSkus[] = $item->getSku();
        }

        return $parentSkus;
    }
}
