<?php

declare(strict_types=1);

use Divante\VsbridgeIndexerCore\Config\IndicesSettings;
use Divante\VsbridgeIndexerCore\Index\IndexSettings;
use Divante\VsbridgeIndexerCore\Index\Indicies\Config;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class IndexSettingsTest responsible for testing \Divante\VsbridgeIndexerCore\Index\IndexSettings
 */
class IndexSettingsTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $indicesSettingsMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $indicesConfigMock;

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
    private $configIndexSettings;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->storeManagerMock = $this->getMockBuilder(
            StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();

        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->indicesSettingsMock = $this->createMock(IndicesSettings::class);
        $this->indicesConfigMock = $this->createMock(Config::class);

        $this->configIndexSettings = $this->objectManager->getObject(
            IndexSettings::class,
            [
                'indicesConfig' => $this->indicesConfigMock,
                'settingConfig' => $this->indicesSettingsMock,
                '$storeManager' =>  $this->storeMock,
            ]
        );
    }

    /**
     * @param string $storeCode
     *
     * @dataProvider provideStores
     */
    public function testIndexAlias(string $storeCode)
    {
        $indexPrefix = 'vuestorefront';
        $this->indicesSettingsMock->expects($this->once())
            ->method('addIdentifierToDefaultStoreView')
            ->willReturn(true);
        $this->indicesSettingsMock->expects($this->once())
            ->method('getIndexNamePrefix')
            ->willReturn($indexPrefix);

        $this->indicesSettingsMock->expects($this->once())
            ->method('getIndexIdentifier')
            ->willReturn('code');

        $this->storeMock->expects($this->once())
            ->method('getCode')
            ->willReturn($storeCode);

        $expectedAlias = strtolower(sprintf('%s_%s', $indexPrefix, $storeCode));

        $this->assertEquals($expectedAlias,
            $this->configIndexSettings->getIndexAlias($this->storeMock)
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
