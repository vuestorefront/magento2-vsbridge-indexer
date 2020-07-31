<?php

namespace Divante\VsbridgeIndexerCatalog\Test\Model\Attributes;

use Divante\VsbridgeIndexerCatalog\Model\Attributes\CategoryAttributes;
use Divante\VsbridgeIndexerCatalog\Model\Attributes\CategoryChildAttributes;
use Divante\VsbridgeIndexerCatalog\Model\SystemConfig\CategoryConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class CategoryChildAttributesTest
 */
class CategoryChildAttributesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var
     */
    private $objectManager;

    /**
     * @var CategoryConfigInterface
     */
    private $catalogConfigMock;

    /**
     * @var CategoryChildAttributes
     */
    private $categoryChildAttributes;

    /**
     *
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->catalogConfigMock = $this->createMock(CategoryConfigInterface::class);
        $this->categoryChildAttributes = $this->objectManager->getObject(
            CategoryChildAttributes::class,
            ['categoryConfig' => $this->catalogConfigMock]
        );
    }

    /**
     * @param int $storeId
     * @param array $selectedAttributes
     *
     * @dataProvider provideAllowedAttributes
     */
    public function testGetChildrenRequiredAttributes(int $storeId, array $selectedAttributes)
    {
        $attributes = CategoryAttributes::MINIMAL_ATTRIBUTE_SET;

        $this->catalogConfigMock->expects($this->once())
            ->method('getAllowedChildAttributesToIndex')
            ->with($storeId)
            ->willReturn($selectedAttributes);

        $productAttributes = $this->categoryChildAttributes->getRequiredAttributes($storeId);

        foreach ($attributes as $attributeCode) {
            $this->assertContains($attributeCode, $productAttributes);
        }
    }

    /**
     *
     */
    public function testGetAllAttributes()
    {
        $storeId = 1;

        $this->catalogConfigMock->expects($this->once())
            ->method('getAllowedChildAttributesToIndex')
            ->willReturn([]);

        $productAttributes = $this->categoryChildAttributes->getRequiredAttributes($storeId);
        $this->assertEmpty($productAttributes);
    }

    /**
     * @return array
     */
    public function provideAllowedAttributes()
    {
        return [
            [
                'storeId' => 1,
                'attributes' => [
                    'name',
                    'is_active',
                    'url_path',
                    'url_key',
                ]
            ],
            [
                'storeId' => 1,
                'attributes' => [
                    'image',
                ]
            ]
        ];
    }
}
