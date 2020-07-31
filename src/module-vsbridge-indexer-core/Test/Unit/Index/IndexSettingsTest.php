<?php

declare(strict_types=1);

use Divante\VsbridgeIndexerCore\Config\IndicesSettings;
use Divante\VsbridgeIndexerCore\Index\IndexSettings;
use Divante\VsbridgeIndexerCore\Index\Indicies\Config;
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
    private $configurationSettings;

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
        $this->storeManagerMock = $this->getMockBuilder(
            StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();

        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->indicesSettingsMock = $this->createMock(IndicesSettings::class);
        $this->configurationSettings = $this->createMock(Config::class);

        $this->esIndexSettings = new IndexSettings(
            $this->storeManagerMock,
            $this->configurationSettings,
            $this->indicesSettingsMock,
            new DateTimeFactory()
        );
    }

    /**
     * @param string $storeCode
     *
     * @dataProvider provideStores
     */
    public function testGetIndexAlias(string $storeCode)
    {
        $indexPrefix = 'vuestorefront';
        $this->indicesSettingsMock->method('addIdentifierToDefaultStoreView')->willReturn(true);
        $this->indicesSettingsMock->method('getIndexNamePrefix')->willReturn($indexPrefix);
        $this->indicesSettingsMock->method('getIndexIdentifier')->willReturn('code');
        $this->storeMock->method('getCode')->willReturn($storeCode);

        $expectedAlias = strtolower(sprintf('%s_%s', $indexPrefix, $storeCode));

        $this->assertEquals(
            $expectedAlias,
            $this->esIndexSettings->getIndexAlias($this->storeMock)
        );
    }

    /**
     * @return array
     */
    public function provideStores()
    {
        return [
            ['de_code'],
            ['De_code'],
            ['DE_CODE'],
        ];
    }
}
