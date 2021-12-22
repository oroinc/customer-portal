<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\CustomerBundle\Doctrine\SoftDeleteableFilter;
use Oro\Bundle\CustomerBundle\Doctrine\SoftDeleteableInterface;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\RFPBundle\Entity\Request;
use Oro\Bundle\RFPBundle\Tests\Functional\DataFixtures\LoadRequestData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\Testing\QueryTracker;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods).
 */
class SoftDeletableFilterTest extends WebTestCase
{
    /** @var QueryTracker */
    private $queryTracker;

    /** @var EntityManager */
    private $em;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
        $this->loadFixtures([LoadRequestData::class]);

        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->queryTracker = new QueryTracker($this->em);
        $this->queryTracker->start();
    }

    protected function tearDown(): void
    {
        $this->queryTracker->stop();
        parent::tearDown();
    }

    public function testFindMethod()
    {
        /** @var Request $request */
        $request = $this->getReferenceRepository()->getReference(LoadRequestData::REQUEST9);
        $this->em->detach($request);

        //FILTER ENABLED
        $this->enableFilter();
        $result = $this->getRepository()
            ->find($request->getId());

        $this->assertNull($result);

        //FILTER DISABLED
        $this->disableFilter();
        $result = $this->getRepository()
            ->find($request->getId());

        $this->assertNotNull($result);

        //CHECK QUERIES
        $this->checkQueries();
    }

    public function testFindByMethod()
    {
        //FILTER ENABLED
        $this->enableFilter();
        $result = $this->getRepository()
            ->findBy(['firstName' => LoadRequestData::FIRST_NAME_DELETED]);

        $this->assertCount(0, $result);

        //FILTER DISABLED
        $this->disableFilter();
        $result = $this->getRepository()
            ->findBy(['firstName' => LoadRequestData::FIRST_NAME_DELETED]);

        $this->assertCount(1, $result);

        //CHECK QUERIES
        $this->checkQueries();
    }

    public function testFindAllMethod()
    {
        //FILTER ENABLED
        $this->enableFilter();
        $result = $this->getRepository()
            ->findAll();

        $this->assertCount(13, $result);

        //FILTER DISABLED
        $this->disableFilter();
        $result = $this->getRepository()
            ->findAll();

        $this->assertCount(14, $result);

        //CHECK QUERIES
        $this->checkQueries();
    }

    public function testInQueryBuilder()
    {
        //FILTER ENABLED
        $this->enableFilter();
        $result = $this->getRepository()
            ->createQueryBuilder('r')
            ->select('r')
            ->join('r.customer', 'a')
            ->where('r.firstName = :name')
            ->setParameter('name', LoadRequestData::FIRST_NAME_DELETED)
            ->getQuery()
            ->execute();

        $this->assertCount(0, $result);

        //FILTER DISABLED
        $this->disableFilter();
        $result = $this->getRepository()
            ->createQueryBuilder('r')
            ->select('r')
            ->join('r.customer', 'a')
            ->where('r.firstName = :name')
            ->setParameter('name', LoadRequestData::FIRST_NAME_DELETED)
            ->getQuery()
            ->execute();

        $this->assertCount(1, $result);

        //CHECK QUERIES
        $this->checkQueries();
    }

    public function testInQueryBuilderJoinRelation()
    {
        //FILTER ENABLED
        $this->enableFilter();
        $result = $this->em->createQueryBuilder()
            ->select('a')
            ->from(Customer::class, 'a')
            ->join(Request::class, 'r', 'WITH', 'a = r.customer')
            ->where('r.firstName = :name')
            ->setParameter('name', 'John')
            ->getQuery()
            ->execute();

        $this->assertCount(0, $result);

        //FILTER DISABLED
        $this->disableFilter();
        $result = $this->em->createQueryBuilder()
            ->select('a')
            ->from(Customer::class, 'a')
            ->join(Request::class, 'r', 'WITH', 'a = r.customer')
            ->where('r.firstName = :name')
            ->setParameter('name', 'John')
            ->getQuery()
            ->execute();
        $this->assertCount(1, $result);

        //CHECK QUERIES
        $this->checkQueries();
    }

    private function enableFilter(): void
    {
        $filters = $this->em->getFilters();
        /** @var SoftDeleteableFilter $filter */
        $filter = $filters->enable(SoftDeleteableFilter::FILTER_ID);
        $filter->setEm($this->em);
    }

    private function disableFilter(): void
    {
        $filters = $this->em->getFilters();
        $filters->disable(SoftDeleteableFilter::FILTER_ID);
    }

    private function assertQueryModified(string $query): void
    {
        $needle = $this->getQueryNeedleString();
        self::assertStringContainsString($needle, $query);
    }

    private function assertQueryNotModified(string $query): void
    {
        $needle = $this->getQueryNeedleString();
        self::assertStringNotContainsString($needle, $query);
    }

    private function getQueryNeedleString(): string
    {
        $connection = $this->em->getConnection();
        $platform = $connection->getDatabasePlatform();
        $metadata = $this->em->getClassMetadata(Request::class);

        $column = $this->em
            ->getConfiguration()
            ->getQuoteStrategy()
            ->getColumnName(SoftDeleteableInterface::FIELD_NAME, $metadata, $platform);

        return $platform->getIsNullExpression($column);
    }

    private function getRepository(): EntityRepository
    {
        return $this->em->getRepository(Request::class);
    }

    private function checkQueries(): void
    {
        $queries = $this->queryTracker->getExecutedQueries();
        $this->assertQueryModified($queries[0]);
        $this->assertQueryNotModified($queries[1]);
    }
}
