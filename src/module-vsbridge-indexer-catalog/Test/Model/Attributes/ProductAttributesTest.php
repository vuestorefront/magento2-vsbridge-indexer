<?php

namespace Divante\VsbridgeIndexerCatalog\Test\Model\Attributes;

use Divante\VsbridgeIndexerCatalog\Model\Attributes\ProductAttributes;
use Divante\VsbridgeIndexerCatalog\Api\CatalogConfigurationInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class ProductAttributesTest
 */
class ProductAttributesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var
     */
    private $objectManager;

    /**
     * @var CatalogConfigurationInterface
     */
    private $catalogConfigMock;

    /**
     * @var ProductAttributes
     */
    private $productAttributes;

    /**
     *
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->catalogConfigMock = $this->createMock(CatalogConfigurationInterface::class);
        $this->productAttributes = $this->objectManager->getObject(
            ProductAttributes::class,
            ['catalogConfiguration' => $this->catalogConfigMock]
        );
    }

    /**
     * @param array $selectedAttributes
     *
     * @dataProvider provideAllowedAttributes
     */
    public function testGetAttributes(array $selectedAttributes)
    {
        $attributes = ProductAttributes::REQUIRED_ATTRIBUTES;
        $this->catalogConfigMock->expects($this->once())
            ->method('getAllowedAttributesToIndex')
            ->willReturn($selectedAttributes);

        $productAttributes = $this->productAttributes->getAttributes();

        foreach ($attributes as $attributeCode) {
            $this->assertContains($attributeCode, $productAttributes);
        }
    }

    /**
     *
     */
    public function testGetAllAttributes()
    {
        $this->catalogConfigMock->expects($this->once())
            ->method('getAllowedAttributesToIndex')
            ->willReturn([]);

        $productAttributes = $this->productAttributes->getAttributes();
        $this->assertEmpty($productAttributes);
    }

    /**
     * @return array
     */
    public function provideAllowedAttributes()
    {
        return [
            [
                [
                    'sku',
                    'url_path',
                    'url_key',
                    'name',
                    'price',
                    'visibility',
                    'status',
                    'price_type',
                ]
            ]
        ];
    }
}
