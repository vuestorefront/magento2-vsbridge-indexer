<?php

namespace Divante\VsbridgeIndexerCatalog\Test\Model;

use Divante\VsbridgeIndexerCatalog\Api\CatalogConfigurationInterface;
use Divante\VsbridgeIndexerCatalog\Model\Product\LoadTierPrices;
use Divante\VsbridgeIndexerCatalog\Model\ProductMetaData;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\AttributeDataProvider;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\TierPrices;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class LoadTierPricesTest extends TestCase
{

    private $attributeDataProviderMock;

    /**
     * @var TierPrices
     */
    private $tierPriceResourceMock;

    /**
     * @var StoreManagerInterface
     */
    private $storeManagerMock;

    /**
     * @var ProductMetaData
     */
    private $productMetaDataMock;

    /**
     * @var CatalogConfigurationInterface
     */
    private $configSettingsMock;

    private $loadTierPrices;

    private $storeMock;

    private $attributeMock;


    protected function setup()
    {
        $this->attributeDataProviderMock = $this->getMockBuilder(AttributeDataProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->tierPriceResourceMock = $this->getMockBuilder(TierPrices::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productMetaDataMock = $this->getMockBuilder(ProductMetaData::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configSettingsMock = $this->getMockBuilder(CatalogConfigurationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeMock = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loadTierPrices = new LoadTierPrices(
            $this->configSettingsMock,
            $this->tierPriceResourceMock,
            $this->storeManagerMock,
            $this->productMetaDataMock,
            $this->attributeDataProviderMock
        );
    }

    public function testGetWebsiteIdWhenScopeIsNotGlobal()
    {
        $storeId = 1;
        $reflector = new ReflectionClass( LoadTierPrices::class );
        $method = $reflector->getMethod( 'getWebsiteId' );
        $method->setAccessible( true );

        $this->attributeMock->method('isScopeGlobal')->willReturn(false);

        $this->storeManagerMock->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->method('getWebsiteId')->willReturn(1);

        $this->attributeDataProviderMock->method('getAttributeByCode')
            ->willReturn($this->attributeMock);

        $result = $method->invokeArgs( $this->loadTierPrices, array( $storeId ) );
        $this->assertEquals( 1, $result );
    }

    public function testGetWebsiteIdWhenScopeIsGlobal()
    {
        $storeId = 1;
        $reflector = new ReflectionClass( LoadTierPrices::class );
        $method = $reflector->getMethod( 'getWebsiteId' );
        $method->setAccessible( true );

        $this->attributeDataProviderMock->method('getAttributeByCode')
            ->willReturn($this->attributeMock);

        $this->attributeMock->method('isScopeGlobal')->willReturn(true);
        $result = $method->invokeArgs( $this->loadTierPrices, array( $storeId ) );
        $this->assertEquals( 0, $result );
    }
}
