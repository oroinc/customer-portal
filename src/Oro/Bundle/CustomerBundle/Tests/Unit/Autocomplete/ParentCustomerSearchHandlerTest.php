<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Autocomplete;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Autocomplete\ParentCustomerSearchHandler;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerRepository;
use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use Oro\Bundle\SearchBundle\Engine\Indexer;
use Oro\Bundle\SearchBundle\Provider\SearchMappingProvider;
use Oro\Bundle\SearchBundle\Query\Criteria\Criteria;
use Oro\Bundle\SearchBundle\Query\Query;
use Oro\Bundle\SearchBundle\Query\Result;
use Oro\Bundle\SearchBundle\Query\Result\Item;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class ParentCustomerSearchHandlerTest extends \PHPUnit\Framework\TestCase
{
    private const TEST_ENTITY_CLASS = 'TestEntity';

    /** @var ParentCustomerSearchHandler */
    private $searchHandler;

    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $managerRegistry;

    /** @var EntityManager|\PHPUnit\Framework\MockObject\MockObject */
    private $entityManager;

    /** @var EntityRepository|\PHPUnit\Framework\MockObject\MockObject */
    private $entityRepository;

    /** @var Indexer|\PHPUnit\Framework\MockObject\MockObject */
    private $indexer;

    /** @var AclHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $aclHelper;

    protected function setUp(): void
    {
        $this->entityRepository = $this->createMock(CustomerRepository::class);
        $this->entityManager = $this->createMock(EntityManager::class);

        $metadataFactory = $this->getMetaMocks();
        $this->entityManager->expects($this->once())
            ->method('getMetadataFactory')
            ->willReturn($metadataFactory);
        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(self::TEST_ENTITY_CLASS)
            ->willReturn($this->entityRepository);

        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->managerRegistry->expects($this->once())
            ->method('getManagerForClass')
            ->with(self::TEST_ENTITY_CLASS)
            ->willReturn($this->entityManager);
        $this->indexer = $this->createMock(Indexer::class);
        $this->aclHelper = $this->createMock(AclHelper::class);

        $searchMappingProvider = $this->createMock(SearchMappingProvider::class);
        $searchMappingProvider->expects($this->once())
            ->method('getEntityAlias')
            ->with(self::TEST_ENTITY_CLASS)
            ->willReturn('alias');

        $this->searchHandler = new ParentCustomerSearchHandler(self::TEST_ENTITY_CLASS, ['name']);
        $this->searchHandler->initSearchIndexer($this->indexer, $searchMappingProvider);
        $this->searchHandler->initDoctrinePropertiesByManagerRegistry($this->managerRegistry);
        $this->searchHandler->setAclHelper($this->aclHelper);
        $this->searchHandler->setPropertyAccessor(PropertyAccess::createPropertyAccessor());
    }

    /**
     * @dataProvider queryWithoutSeparatorDataProvider
     */
    public function testSearchNoSeparator(string $query)
    {
        $this->indexer->expects($this->never())
            ->method($this->anything());
        $result = $this->searchHandler->search($query, 1, 10);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('more', $result);
        $this->assertArrayHasKey('results', $result);
        $this->assertFalse($result['more']);
        $this->assertEmpty($result['results']);
    }

    public function queryWithoutSeparatorDataProvider(): array
    {
        return [
            [''],
            ['test']
        ];
    }

    /**
     * @dataProvider queryWithoutSeparatorDataProvider
     */
    public function testSearchNewCustomer(string $search)
    {
        $page = 1;
        $perPage = 15;
        $queryString = $search . ';';

        $foundElements = [
            $this->getSearchItem(2),
            $this->getSearchItem(1)
        ];

        /**
         * Test correct order
         */
        $resultData = [
            $this->getResultStub(2, 'test2'),
            $this->getResultStub(1, 'test1')
        ];
        $expectedResultData = [
            ['id' => 2, 'name' => 'test2'],
            ['id' => 1, 'name' => 'test1']
        ];
        $expectedIds = [2, 1];

        $this->assertSearchCall(
            $search,
            $page,
            $perPage,
            $foundElements,
            $resultData,
            $expectedIds,
            []
        );

        $searchResult = $this->searchHandler->search($queryString, $page, $perPage);
        $this->assertIsArray($searchResult);
        $this->assertArrayHasKey('more', $searchResult);
        $this->assertArrayHasKey('results', $searchResult);
        $this->assertSame($expectedResultData, $searchResult['results']);
    }

    /**
     * @dataProvider queryWithoutSeparatorDataProvider
     */
    public function testSearchExistingCustomer(string $search)
    {
        $page = 1;
        $perPage = 15;
        $customerId = 2;
        $queryString = $search . ';' . $customerId;

        $foundElements = [
            $this->getSearchItem(3),
            $this->getSearchItem(1)
        ];
        $resultData = [
            $this->getResultStub(3, 'test3'),
            $this->getResultStub(1, 'test1'),
        ];
        $expectedResultData = [
            ['id' => 3, 'name' => 'test3'],
            ['id' => 1, 'name' => 'test1']
        ];
        $expectedIds = [3,1];

        $this->entityRepository->expects($this->once())
            ->method('getChildrenIds')
            ->with($customerId, $this->aclHelper)
            ->willReturn([]);

        $this->assertSearchCall(
            $search,
            $page,
            $perPage,
            $foundElements,
            $resultData,
            $expectedIds,
            [2]
        );

        $searchResult = $this->searchHandler->search($queryString, $page, $perPage);
        $this->assertIsArray($searchResult);
        $this->assertArrayHasKey('more', $searchResult);
        $this->assertArrayHasKey('results', $searchResult);
        $this->assertSame($expectedResultData, $searchResult['results']);
    }

    /**
     * @dataProvider queryWithoutSeparatorDataProvider
     */
    public function testSearchExistingCustomerWithChildren(string $search)
    {
        $page = 1;
        $perPage = 15;
        $customerId = 2;
        $queryString = $search . ';' . $customerId;
        $foundElements = [
            $this->getSearchItem(4),
            $this->getSearchItem(1)
        ];
        $resultData = [
            $this->getResultStub(4, 'test4'),
            $this->getResultStub(1, 'test1')
        ];
        $expectedResultData = [
            ['id' => 4, 'name' => 'test4'],
            ['id' => 1, 'name' => 'test1']
        ];
        $expectedIds = [4, 1];

        $this->entityRepository->expects($this->once())
            ->method('getChildrenIds')
            ->with($customerId, $this->aclHelper)
            ->willReturn([3]);

        $this->assertSearchCall(
            $search,
            $page,
            $perPage,
            $foundElements,
            $resultData,
            $expectedIds,
            [3, 2]
        );

        $searchResult = $this->searchHandler->search($queryString, $page, $perPage);
        $this->assertIsArray($searchResult);
        $this->assertArrayHasKey('more', $searchResult);
        $this->assertArrayHasKey('results', $searchResult);
        $this->assertSame($expectedResultData, $searchResult['results']);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function getMetaMocks()
    {
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects($this->once())
            ->method('getSingleIdentifierFieldName')
            ->willReturn('id');
        $metadataFactory = $this->createMock(ClassMetadataFactory::class);
        $metadataFactory->expects($this->once())
            ->method('getMetadataFor')
            ->with(self::TEST_ENTITY_CLASS)
            ->willReturn($metadata);

        return $metadataFactory;
    }

    private function getSearchItem(int $id): Item
    {
        $element = $this->createMock(Item::class);
        $element->expects($this->once())
            ->method('getRecordId')
            ->willReturn($id);

        return $element;
    }

    private function getResultStub(int $id, string $name): \stdClass
    {
        $result = new \stdClass();
        $result->id = $id;
        $result->name = $name;

        return $result;
    }

    private function assertSearchCall(
        string $search,
        int $page,
        int $perPage,
        array $foundElements,
        array $resultData,
        array $expectedIds,
        array $excludedIds
    ): void {
        $query = $this->createMock(Query::class);
        $this->indexer->expects($this->once())
            ->method('getSimpleSearchQuery')
            ->with($search, $page - 1, $perPage + 1, 'alias')
            ->willReturn($query);

        if (count($excludedIds) > 0) {
            $field = Criteria::implodeFieldTypeName(Query::TYPE_INTEGER, 'oro_customer_id');

            $criteria = $this->createMock(Criteria::class);
            $query->expects($this->once())
                ->method('getCriteria')
                ->willReturn($criteria);

            $criteria->expects($this->once())
                ->method('andWhere')
                ->with(Criteria::expr()->notIn($field, $excludedIds));
        } else {
            $query->expects($this->never())
                ->method('getCriteria');
        }

        $searchResult = $this->createMock(Result::class);
        $searchResult->expects($this->once())
            ->method('getElements')
            ->willReturn($foundElements);
        $this->indexer->expects($this->once())
            ->method('query')
            ->with($query)
            ->willReturn($searchResult);

        $queryBuilder = $this->createMock(QueryBuilder::class);

        $query = $this->createMock(AbstractQuery::class);
        $query->expects($this->once())
            ->method('getResult')
            ->willReturn($resultData);

        $expr = $this->createMock(Expr::class);
        $expr->expects($this->once())
            ->method('in')
            ->with('e.id', ':entityIds')
            ->willReturnSelf();
        $queryBuilder->expects($this->once())
            ->method('setParameter')
            ->with('entityIds', $expectedIds)
            ->willReturnSelf();
        $queryBuilder->expects($this->once())
            ->method('expr')
            ->willReturn($expr);
        $queryBuilder->expects($this->once())
            ->method('where')
            ->with($expr)
            ->willReturnSelf();
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);
        $this->entityRepository->expects($this->any())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);
        $this->aclHelper->expects($this->once())
            ->method('apply')
            ->with($query)
            ->willReturn($query);
    }
}
