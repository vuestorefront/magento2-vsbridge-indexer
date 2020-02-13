<?php

namespace Divante\VsbridgeIndexerCatalog\Test\Model\Attributes;

use Divante\VsbridgeIndexerCatalog\Model\Attributes\ConfigurableAttributes;
use Divante\VsbridgeIndexerCatalog\Api\CatalogConfigurationInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class ConfigurableAttributesTest
 */
class ConfigurableAttributesTest extends \PHPUnit\Framework\TestCase
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
     * @var ConfigurableAttributes
     */
    private $configurableAttributes;

    /**
     *
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->catalogConfigMock = $this->createMock(CatalogConfigurationInterface::class);
        $this->configurableAttributes = $this->objectManager->getObject(
            ConfigurableAttributes::class,
            ['catalogConfiguration' => $this->catalogConfigMock]
        );
    }

    /**
     * @param array $selectedAttributes
     *
     * @dataProvider provideAllowedAttributes
     */
    public function testGetChildrenRequiredAttributes(array $selectedAttributes)
    {
        $attributes = ConfigurableAttributes::MINIMAL_ATTRIBUTE_SET;

        $this->catalogConfigMock->expects($this->once())
            ->method('getAllowedChildAttributesToIndex')
            ->willReturn($selectedAttributes);

        $productAttributes = $this->configurableAttributes->getChildrenRequiredAttributes();

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
            ->method('getAllowedChildAttributesToIndex')
            ->willReturn([]);

        $productAttributes = $this->configurableAttributes->getChildrenRequiredAttributes();
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
                    'status',
                    'visibility',
                    'name',
                    'price',
                ]
            ],
            [
                [
                    'tax_class_id',
                ]
            ]
        ];
    }
}
