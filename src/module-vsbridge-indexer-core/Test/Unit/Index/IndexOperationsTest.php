<?php

use Divante\VsbridgeIndexerCore\Api\Client\ClientInterface;
use Divante\VsbridgeIndexerCore\Index\IndexOperations;
use Divante\VsbridgeIndexerCore\Index\IndexSettings;
use Divante\VsbridgeIndexerCore\Api\BulkResponseInterfaceFactory as BulkResponseFactory;
use Divante\VsbridgeIndexerCore\Api\BulkRequestInterfaceFactory as BulkRequestFactory;
use Divante\VsbridgeIndexerCore\Api\IndexInterfaceFactory as IndexFactory;
use Divante\VsbridgeIndexerCore\Index\Index;
use Divante\VsbridgeIndexerCore\Elasticsearch\ClientResolver;
use PHPUnit\Framework\TestCase;
use Magento\Store\Model\Store;

/**
 * Class IndexOperationsTest
 */
class IndexOperationsTest extends TestCase
{
    /**
     * @var Store|PHPUnit_Framework_MockObject_MockObject
     */
    private $storeMock;

    /**
     * @var IndexSettings|PHPUnit_Framework_MockObject_MockObject
     */
    private $esIndexSettingsMock;

    /**
     * @var ClientResolver
     */
    private $clientResolverMock;

    /**
     * @var IndexFactory
     */
    private $indexFactoryMock;

    /**
     * @var BulkResponseFactory
     */
    private $bulkResponseFactoryMock;

    /**
     * @var BulkRequestFactory
     */
    private $bulkRequestFactoryMock;

    /**
     * @var IndexOperations
     */
    private $indexOperations;

    /** @var PHPUnit_Framework_MockObject_MockObject  */
    private $clientMock;

    /** @var array[][]  */
    private $indicesXmlConfiguration  = [
        'vue' => [
            'types' => []
        ]
    ];

    protected function setUp()
    {
        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->esIndexSettingsMock = $this->getMockBuilder(IndexSettings::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->bulkRequestFactoryMock = $this->getMockBuilder(BulkRequestFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->bulkResponseFactoryMock = $this->getMockBuilder(BulkResponseFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->indexFactoryMock = $this->getMockBuilder(IndexFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->clientMock = $this->getMockBuilder(
            ClientInterface::class
        )->disableOriginalConstructor()->getMock();

        $this->clientResolverMock = $this->getMockBuilder(ClientResolver::class)
                ->disableOriginalConstructor()
                ->getMock();

        $this->indexOperations = new IndexOperations(
            $this->clientResolverMock,
            $this->bulkResponseFactoryMock,
            $this->bulkRequestFactoryMock,
            $this->esIndexSettingsMock,
            $this->indexFactoryMock
        );
    }

    public function testGetExistingIndex()
    {
        $alias =  'vuestorefront_1';
        $name = 'vuestorefront_1';

        $indexMock = new Index(
            $name,
            $alias,
            []
        );

        $this->indexFactoryMock
            ->method('create')
            ->with([
                'name' => $name,
                'alias' => $alias,
                'types' => [],
            ])
            ->willReturn($indexMock);

        $this->esIndexSettingsMock->method('getIndicesConfig')->willReturn($this->indicesXmlConfiguration);
        $this->esIndexSettingsMock->method('getIndexAlias')->willReturn($alias);
        $this->esIndexSettingsMock->method('createIndexName')->willReturn($name);
        $this->esIndexSettingsMock->method('getEsConfig')->willReturn([]);
        $this->clientResolverMock->method('getClient')->with(1)->willReturn($this->clientMock);
        $this->storeMock->method('getId')->willReturn(1);

        $this->clientMock->method('indexExists')->with($name)->willReturn(true);

        $index = $this->indexOperations->getIndexByName('vue', $this->storeMock);
        $this->assertEquals($indexMock, $index);
        $this->assertEquals($index->isNew(), false);
    }

    public function testCreateNewIndex()
    {
        $this->storeMock->method('getId')->willReturn(1);

        $alias =  'vuestorefront_de';
        $name = 'vuestorefront_1';

        $indexMock = new Index(
            $name,
            $alias,
            []
        );

        $this->indexFactoryMock
            ->method('create')
            ->with([
                'name' => $name,
                'alias' => $alias,
                'types' => [],
            ])
            ->willReturn($indexMock);

        $this->esIndexSettingsMock->method('getIndicesConfig')->willReturn($this->indicesXmlConfiguration);
        $this->esIndexSettingsMock->method('getIndexAlias')->willReturn($alias);
        $this->esIndexSettingsMock->method('createIndexName')->willReturn($name);
        $this->esIndexSettingsMock->method('getEsConfig')->willReturn([]);
        $this->clientResolverMock->method('getClient')->with(1)->willReturn($this->clientMock);

        $index = $this->indexOperations->createIndex('vue', $this->storeMock);
        $this->assertEquals($indexMock, $index);
        $this->assertEquals($index->isNew(), true);
    }
}
