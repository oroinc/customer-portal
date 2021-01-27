<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Autocomplete;

use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\Value;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Autocomplete\CustomerUserSearchHandler;
use Oro\Bundle\SearchBundle\Engine\Indexer;
use Oro\Bundle\SearchBundle\Provider\SearchMappingProvider;
use Oro\Bundle\SearchBundle\Query\Criteria\Criteria;
use Oro\Bundle\SearchBundle\Query\Query as SearchQuery;
use Oro\Bundle\SearchBundle\Query\Result;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class CustomerUserSearchHandlerTest extends \PHPUnit\Framework\TestCase
{
    private const DELIMITER = ';';

    private const TEST_ENTITY_CLASS = 'TestCustomerUserEntity';

    private const CUSTOMER_ID = '1';

    private const PAGE = 1;

    private const PER_PAGE = 5;

    /** @var EntityManager|\PHPUnit\Framework\MockObject\MockObject */
    private $entityManager;

    /** @var EntityRepository|\PHPUnit\Framework\MockObject\MockObject */
    private $entityRepository;

    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $managerRegistry;

    /** @var Indexer|\PHPUnit\Framework\MockObject\MockObject */
    private $indexer;

    /** @var AclHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $aclHelper;

    /** @var CustomerUserSearchHandler */
    private $searchHandler;

    protected function setUp(): void
    {
        $this->indexer = self::createMock(Indexer::class);

        /* @var $metadata ClassMetadata|\PHPUnit\Framework\MockObject\MockObject */
        $metadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->setMethods(['getSingleIdentifierFieldName'])
            ->disableOriginalConstructor()
            ->getMock();
        $metadata->expects(self::once())
            ->method('getSingleIdentifierFieldName')
            ->will($this->returnValue('id'));

        /* @var $metadataFactory ClassMetadataFactory|\PHPUnit\Framework\MockObject\MockObject */
        $metadataFactory = self::createMock(ClassMetadataFactory::class);
        $metadataFactory->expects(self::once())
            ->method('getMetadataFor')
            ->with(self::TEST_ENTITY_CLASS)
            ->will($this->returnValue($metadata));

        $this->entityManager = self::createMock(EntityManager::class);
        $this->entityManager->expects(self::once())
            ->method('getMetadataFactory')
            ->willReturn($metadataFactory);

        $this->entityRepository = self::createMock(EntityRepository::class);
        $this->entityManager->expects(self::once())
            ->method('getRepository')
            ->with(self::TEST_ENTITY_CLASS)
            ->willReturn($this->entityRepository);

        $this->aclHelper = self::createMock(AclHelper::class);
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->managerRegistry->expects(self::once())
            ->method('getManagerForClass')
            ->with(self::TEST_ENTITY_CLASS)
            ->willReturn($this->entityManager);

        $searchMappingProvider = $this->createMock(SearchMappingProvider::class);
        $searchMappingProvider->expects($this->once())
            ->method('getEntityAlias')
            ->with(self::TEST_ENTITY_CLASS)
            ->willReturn('alias');

        $this->searchHandler = new CustomerUserSearchHandler(self::TEST_ENTITY_CLASS, ['email']);
        $this->searchHandler->initSearchIndexer($this->indexer, $searchMappingProvider);
        $this->searchHandler->initDoctrinePropertiesByManagerRegistry($this->managerRegistry);
        $this->searchHandler->setAclHelper($this->aclHelper);
    }

    public function testSearchWithoutDelimiter(): void
    {
        self::assertEquals(
            $this->getExpectedResult(),
            $this->searchHandler->search('', self::PAGE, self::PER_PAGE)
        );
    }

    public function testSearch(): void
    {
        $this->assertSearchEntities();
        $this->assertSearchIdsByTermAndCustomer();

        $search = sprintf('%s%s%s', 'search', self::DELIMITER, self::CUSTOMER_ID);
        self::assertEquals(
            $this->getExpectedResult([['id' => 1, 'email' => 'acme1']]),
            $this->searchHandler->search($search, self::PAGE, self::PER_PAGE)
        );
    }

    public function testSearchWithoutCustomer(): void
    {
        $this->assertSearchEntitiesWithoutCustomer();
        $this->assertSearchIdsByTermAndCustomerIsNull();

        $search = sprintf('%s%s', 'search', self::DELIMITER);
        self::assertEquals(
            $this->getExpectedResult([['id' => 1, 'email' => 'acme1']]),
            $this->searchHandler->search($search, self::PAGE, self::PER_PAGE)
        );
    }

    private function assertSearchIdsByTermAndCustomer(): void
    {
        $queryResult = self::createMock(Result::class);
        $queryResult->expects(self::once())
            ->method('getElements')
            ->willReturn([$this->getResultItem(1)]);

        $criteria = self::createMock(Criteria::class);
        $criteria->expects(self::once())
            ->method('andWhere')
            ->with(new Comparison('integer.customer_id', Comparison::EQ, new Value(self::CUSTOMER_ID)))
            ->willReturnSelf();

        $searchQuery = self::createMock(SearchQuery::class);
        $searchQuery->expects(self::once())
            ->method('getCriteria')
            ->willReturn($criteria);

        $this->indexer->expects(self::once())
            ->method('getSimpleSearchQuery')
            ->willReturn($searchQuery);

        $this->indexer->expects(self::once())
            ->method('query')
            ->willReturn($queryResult);
    }

    private function assertSearchIdsByTermAndCustomerIsNull(): void
    {
        $queryResult = self::createMock(Result::class);
        $queryResult->expects(self::once())
            ->method('getElements')
            ->willReturn([$this->getResultItem(1)]);

        $searchQuery = self::createMock(SearchQuery::class);
        $searchQuery->expects(self::never())
            ->method('getCriteria');

        $this->indexer->expects(self::once())
            ->method('getSimpleSearchQuery')
            ->willReturn($searchQuery);

        $this->indexer->expects(self::once())
            ->method('query')
            ->willReturn($queryResult);
    }

    private function assertSearchEntities(): void
    {
        /* @var $expr Expr|\PHPUnit\Framework\MockObject\MockObject */
        $expr = self::createMock(Expr::class);
        $expr->expects(self::once())
            ->method('asc')
            ->willReturnSelf();

        $expr->expects(self::once())
            ->method('in')
            ->willReturnSelf();

        /* @var $queryBuilder QueryBuilder|\PHPUnit\Framework\MockObject\MockObject */
        $queryBuilder = self::createMock(QueryBuilder::class);

        $queryBuilder->expects(self::exactly(2))
            ->method('expr')
            ->willReturn($expr);

        $queryBuilder->expects(self::once())
            ->method('where')
            ->with($expr)
            ->willReturnSelf();

        $queryBuilder->expects(self::once())
            ->method('addOrderBy')
            ->with($expr)
            ->willReturnSelf();

        $queryBuilder->expects(self::once())
            ->method('andWhere')
            ->with('e.customer = :customer')
            ->willReturnSelf();

        $this->entityRepository
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $query = self::createMock(AbstractQuery::class);
        $query->expects(self::once())
            ->method('getResult')
            ->willReturn([$this->getResultStub(1, 'acme1')]);

        $this->aclHelper->expects(self::once())
            ->method('apply')
            ->with($queryBuilder, 'VIEW')
            ->willReturn($query);
    }

    private function assertSearchEntitiesWithoutCustomer(): void
    {
        /* @var $expr Expr|\PHPUnit\Framework\MockObject\MockObject */
        $expr = self::createMock(Expr::class);
        $expr->expects(self::once())
            ->method('asc')
            ->willReturnSelf();

        $expr->expects(self::once())
            ->method('in')
            ->willReturnSelf();

        /* @var $queryBuilder QueryBuilder|\PHPUnit\Framework\MockObject\MockObject */
        $queryBuilder = self::createMock(QueryBuilder::class);

        $queryBuilder->expects(self::exactly(2))
            ->method('expr')
            ->willReturn($expr);

        $queryBuilder->expects(self::once())
            ->method('where')
            ->with($expr)
            ->willReturnSelf();

        $queryBuilder->expects(self::once())
            ->method('addOrderBy')
            ->with($expr)
            ->willReturnSelf();

        $queryBuilder->expects(self::never())->method('andWhere');

        $this->entityRepository
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $query = self::createMock(AbstractQuery::class);
        $query->expects(self::once())
            ->method('getResult')
            ->willReturn([$this->getResultStub(1, 'acme1')]);

        $this->aclHelper->expects(self::once())
            ->method('apply')
            ->with($queryBuilder, 'VIEW')
            ->willReturn($query);
    }

    /**
     * @param array $result
     * @param bool $hasMore
     *
     * @return array
     */
    private function getExpectedResult($result = [], $hasMore = false): array
    {
        return [
            'results' => $result,
            'more' => $hasMore
        ];
    }

    /**
     * @param int $id
     *
     * @return Result\Item|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getResultItem($id)
    {
        $element = self::createMock(Result\Item::class);
        $element->expects(self::once())
            ->method('getRecordId')
            ->willReturn($id);

        return $element;
    }

    /**
     * @param int $id
     * @param string $email
     *
     * @return \stdClass
     */
    private function getResultStub($id, $email)
    {
        $result = new \stdClass();
        $result->id = $id;
        $result->email = $email;

        return $result;
    }

    public function testFindById()
    {
        $customerId = 1;
        $search = 'test';
        $foundElement = $this->getResultStub($customerId, $search);
        $expectedResultData = [
            ['id' => $customerId, 'email' => $search],
        ];
        $queryString = sprintf('%s%s%d', $search, self::DELIMITER, $customerId);

        $this->entityRepository->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue($foundElement));

        $searchResult = $this->searchHandler->search($queryString, 1, 10, true);

        $this->assertIsArray($searchResult);
        $this->assertArrayHasKey('more', $searchResult);
        $this->assertArrayHasKey('results', $searchResult);
        $this->assertEquals($expectedResultData, $searchResult['results']);
    }
}
