<?php

declare(strict_types=1);

namespace Divante\VsbridgeIndexerCore\Test\Unit\Index;

use Divante\VsbridgeIndexerCore\Config\IndicesSettings;
use Divante\VsbridgeIndexerCore\Index\IndexSettings;
use Divante\VsbridgeIndexerCore\Index\Indices\ConfigResolver;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Intl\DateTimeFactory;

/**
 * Class IndexSettingsTest responsible for testing \Divante\VsbridgeIndexerCore\Index\IndexSettings
 */
class IndexSettingsTest extends TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $indicesSettingsMock;

    /**
     * @var IndicesSettings|PHPUnit_Framework_MockObject_MockObject
     */
    private $configResolver;

    /**
     * @var Store|PHPUnit_Framework_MockObject_MockObject
     */
    private $storeMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var IndexSettings
     */
    private $esIndexSettings;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();

        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->indicesSettingsMock = $this->createMock(IndicesSettings::class);
        $this->configResolver = $this->createMock(ConfigResolver::class);

        $this->esIndexSettings = new IndexSettings(
            $this->storeManagerMock,
            $this->configResolver,
            $this->indicesSettingsMock,
            new DateTimeFactory()
        );
    }

    /**
     * @param string $storeCode
     *
     * @dataProvider provideStores
     */
    public function testGetIndexAlias(string $identifier, string $storeCode)
    {
        $indexPrefix = 'vuestorefront';
        $this->indicesSettingsMock->method('addIdentifierToDefaultStoreView')->willReturn(true);
        $this->indicesSettingsMock->method('getIndexNamePrefix')->willReturn($indexPrefix);
        $this->indicesSettingsMock->method('getIndexIdentifier')->willReturn('code');
        $this->storeMock->method('getCode')->willReturn($storeCode);

        $indexPrefix .= $identifier === IndexSettings::DUMMY_INDEX_IDENTIFIER ? '' : '_' . $identifier;
        $expectedAlias = strtolower(sprintf('%s_%s', $indexPrefix, $storeCode));

        $this->assertEquals(
            $expectedAlias,
            $this->esIndexSettings->getIndexAlias($identifier, $this->storeMock)
        );
    }

    /**
     * @return array
     */
    public function provideStores()
    {
        return [
            ['vue_storefront_catalog', 'de_code'],
            ['vue_storefront_catalog', 'De_code'],
            ['vue_storefront_catalog', 'DE_CODE'],
            ['product', 'de_code'],
            ['product', 'de_code'],
            ['product', 'DE_CODE'],
        ];
    }
}
