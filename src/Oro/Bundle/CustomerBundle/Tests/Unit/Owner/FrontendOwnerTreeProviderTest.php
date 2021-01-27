<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Owner;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Async\Topics;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Model\OwnerTreeMessageFactory;
use Oro\Bundle\CustomerBundle\Owner\FrontendOwnerTreeProvider;
use Oro\Bundle\EntityBundle\Tools\DatabaseChecker;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;
use Oro\Bundle\SecurityBundle\Owner\OwnerTree;
use Oro\Bundle\SecurityBundle\Tests\Util\ReflectionUtil;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessageProducer;
use Oro\Component\TestUtils\ORM\Mocks\ConnectionMock;
use Oro\Component\TestUtils\ORM\Mocks\DriverMock;
use Oro\Component\TestUtils\ORM\Mocks\EntityManagerMock;
use Oro\Component\TestUtils\ORM\OrmTestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @SuppressWarnings(PHPMD)
 */
class FrontendOwnerTreeProviderTest extends OrmTestCase
{
    const ENTITY_NAMESPACE = 'Oro\Bundle\CustomerBundle\Tests\Unit\Owner\Fixtures\Entity';

    const ORG_1 = 1;
    const ORG_2 = 2;

    const MAIN_ACCOUNT_1 = 10;
    const MAIN_ACCOUNT_2 = 20;
    const ACCOUNT_1 = 30;
    const ACCOUNT_2 = 40;
    const ACCOUNT_2_1 = 50;

    const USER_1 = 100;
    const USER_2 = 200;

    /** @var EntityManagerMock */
    protected $em;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|DatabaseChecker
     */
    protected $databaseChecker;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|CacheProvider
     */
    protected $cache;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|OwnershipMetadataProviderInterface
     */
    protected $ownershipMetadataProvider;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|MessageProducer
     */
    protected $messageProducer;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|OwnerTreeMessageFactory
     */
    protected $ownerTreeMessageFactory;

    /**
     * @var FrontendOwnerTreeProvider
     */
    protected $treeProvider;

    /** @var LoggerInterface */
    protected $logger;

    protected function setUp(): void
    {
        $reader = new AnnotationReader();
        $metadataDriver = new AnnotationDriver($reader, self::ENTITY_NAMESPACE);

        $conn = new ConnectionMock([], new DriverMock());
        $conn->setDatabasePlatform(new MySqlPlatform());
        $this->em = $this->getTestEntityManager($conn);
        $this->em->getConfiguration()->setMetadataDriverImpl($metadataDriver);
        $this->em->getConfiguration()->setEntityNamespaces(['Test' => self::ENTITY_NAMESPACE]);

        /** @var \PHPUnit\Framework\MockObject\MockObject|ManagerRegistry $doctrine */
        $doctrine = $this->getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine->expects($this->any())
            ->method('getManagerForClass')
            ->will($this->returnValue($this->em));

        $this->databaseChecker = $this->getMockBuilder(DatabaseChecker::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cache = $this->getMockForAbstractClass(
            CacheProvider::class,
            [],
            '',
            true,
            true,
            true,
            ['fetch', 'save']
        );
        $this->cache->expects($this->any())
            ->method('fetch')
            ->will($this->returnValue(false));
        $this->cache->expects($this->any())
            ->method('save');

        $this->ownershipMetadataProvider = $this->createMock(OwnershipMetadataProviderInterface::class);
        $this->ownershipMetadataProvider->expects($this->any())
            ->method('getUserClass')
            ->willReturn(self::ENTITY_NAMESPACE . '\TestCustomerUser');
        $this->ownershipMetadataProvider->expects($this->any())
            ->method('getBusinessUnitClass')
            ->willReturn(self::ENTITY_NAMESPACE . '\TestCustomer');

        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->messageProducer = $this->createMock(MessageProducer::class);
        $this->ownerTreeMessageFactory = $this->createMock(OwnerTreeMessageFactory::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->treeProvider = new FrontendOwnerTreeProvider(
            $doctrine,
            $this->databaseChecker,
            $this->cache,
            $this->ownershipMetadataProvider,
            $this->tokenStorage,
            $this->messageProducer,
            $this->ownerTreeMessageFactory
        );
        $this->treeProvider->setLogger($this->logger);
    }

    /**
     * @param \PHPUnit\Framework\MockObject\MockObject $conn
     * @param int                                      $expectsAt
     * @param string                                   $sql
     * @param array                                    $result
     */
    protected function setFetchAllQueryExpectationAt(
        \PHPUnit\Framework\MockObject\MockObject $conn,
        $expectsAt,
        $sql,
        $result
    ) {
        $stmt = $this->createMock('Oro\Component\TestUtils\ORM\Mocks\StatementMock');
        $stmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn($result);
        $conn
            ->expects($this->at($expectsAt))
            ->method('query')
            ->with($sql)
            ->will($this->returnValue($stmt));
    }

    /**
     * @param \PHPUnit\Framework\MockObject\MockObject $connection
     * @param string[]                                 $customers
     */
    protected function setGetCustomersExpectation($connection, array $customers)
    {
        $queryResult = [];
        foreach ($customers as $item) {
            $queryResult[] = [
                'id_0'   => $item['id'],
                'sclr_1' => $item['orgId'],
                'sclr_2' => $item['parentId'],
            ];
        }
        $this->setQueryExpectationAt(
            $connection,
            0,
            'SELECT t0_.id AS id_0, t0_.organization_id AS sclr_1, t0_.parent_id AS sclr_2,'
            . ' (CASE WHEN t0_.parent_id IS NULL THEN 0 ELSE 1 END) AS sclr_3'
            . ' FROM tbl_customer t0_'
            . ' ORDER BY sclr_3 ASC, sclr_2 ASC',
            $queryResult
        );
    }

    /**
     * @param \PHPUnit\Framework\MockObject\MockObject $connection
     * @param string[]                                 $users
     */
    protected function setGetUsersExpectation($connection, array $users)
    {
        $queryResult = [];
        foreach ($users as $item) {
            $queryResult[] = [
                'id_0'   => $item['userId'],
                'sclr_1'   => $item['orgId'],
                'sclr_2' => $item['customerId'],
            ];
        }
        $this->setQueryExpectationAt(
            $connection,
            1,
            'SELECT t0_.id AS id_0, t0_.organization_id AS sclr_1, t0_.customer_id AS sclr_2'
            . ' FROM tbl_customer_user t0_'
            . ' ORDER BY sclr_1 ASC',
            $queryResult
        );
    }

    public function testSupportsForSupportedUser()
    {
        $token = $this->createMock(TokenInterface::class);
        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);
        $token->expects(self::atLeastOnce())
            ->method('getUser')
            ->willReturn(new CustomerUser());

        $this->assertTrue($this->treeProvider->supports());
    }

    public function testSupportsForNotSupportedUser()
    {
        $token = $this->createMock(TokenInterface::class);
        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);
        $token->expects(self::once())
            ->method('getUser')
            ->willReturn(new User());

        $this->assertFalse($this->treeProvider->supports());
    }

    public function testSupportsWhenNoSecurityToken()
    {
        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn(null);

        $this->assertFalse($this->treeProvider->supports());
    }

    /**
     * @dataProvider addBusinessUnitDirectCyclicRelationProvider
     */
    public function testDirectCyclicRelationshipBetweenBusinessUnits($src, $expected, $criticalMessageArguments)
    {
        $this->logger->expects($this->once())
            ->method('critical')
            ->with(
                sprintf(
                    'Cyclic relationship in "%s" with problem id "%s"',
                    $criticalMessageArguments['businessUnitClass'],
                    $criticalMessageArguments['buId']
                )
            );

        /** @var OwnerTree $tree */
        $tree = $this->treeProvider->getTree();
        $businessUnitClass = $this->ownershipMetadataProvider->getBusinessUnitClass();
        $subordinateBusinessUnitIds = ReflectionUtil::callProtectedMethod(
            $this->treeProvider,
            'buildTree',
            [$src, $businessUnitClass]
        );

        foreach ($subordinateBusinessUnitIds as $parentBusinessUnit => $businessUnits) {
            $tree->setSubordinateBusinessUnitIds($parentBusinessUnit, $businessUnits);
        }

        foreach ($expected as $parentBusinessUnit => $businessUnits) {
            $this->assertEquals($businessUnits, $tree->getSubordinateBusinessUnitIds($parentBusinessUnit));
        }
    }

    /**
     * @dataProvider addBusinessUnitNotDirectCyclicRelationProvider
     */
    public function testNotDirectCyclicRelationshipBetweenBusinessUnits($src, $expected, $criticalMessageArguments)
    {
        $this->logger->expects($this->exactly(count($criticalMessageArguments)))
            ->method('critical')
            ->withConsecutive(
                [sprintf(
                    'Cyclic relationship in "%s" with problem id "%s"',
                    $criticalMessageArguments[0]['businessUnitClass'],
                    $criticalMessageArguments[0]['buId']
                )],
                [sprintf(
                    'Cyclic relationship in "%s" with problem id "%s"',
                    $criticalMessageArguments[1]['businessUnitClass'],
                    $criticalMessageArguments[1]['buId']
                )],
                [sprintf(
                    'Cyclic relationship in "%s" with problem id "%s"',
                    $criticalMessageArguments[2]['businessUnitClass'],
                    $criticalMessageArguments[2]['buId']
                )]
            );

        /** @var OwnerTree $tree */
        $tree = $this->treeProvider->getTree();
        $businessUnitClass = $this->ownershipMetadataProvider->getBusinessUnitClass();
        $subordinateBusinessUnitIds = ReflectionUtil::callProtectedMethod(
            $this->treeProvider,
            'buildTree',
            [$src, $businessUnitClass]
        );

        foreach ($subordinateBusinessUnitIds as $parentBusinessUnit => $businessUnits) {
            $tree->setSubordinateBusinessUnitIds($parentBusinessUnit, $businessUnits);
        }

        foreach ($expected as $parentBusinessUnit => $businessUnits) {
            $this->assertEquals($businessUnits, $tree->getSubordinateBusinessUnitIds($parentBusinessUnit));
        }
    }

    /**
     * @return array
     */
    public function addBusinessUnitDirectCyclicRelationProvider()
    {
        return [
            'direct cyclic relationship' => [
                [
                    2 => 4,
                    1 => null,
                    3 => 1,
                    4 => 2,
                    5 => 1,
                    6 => 5
                ],
                [
                    1 => [3, 5, 6],
                    5 => [6]
                ],
                [

                    'businessUnitClass' => self::ENTITY_NAMESPACE . '\TestCustomer',
                    'buId' => 2

                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function addBusinessUnitNotDirectCyclicRelationProvider()
    {
        return [
            'not direct cyclic relationship' => [
                [
                    1  => null,
                    3  => 1,
                    4  => 1,
                    5 => 7,
                    6 => 5,
                    7  => 6,
                    8 => 14,
                    11 =>8,
                    12 => 11,
                    13 => 12,
                    14 => 13
                ],
                [
                    1 => [3, 4]
                ],
                [
                    [
                        'businessUnitClass' => self::ENTITY_NAMESPACE . '\TestCustomer',
                        'buId' => 5
                    ],
                    [
                        'businessUnitClass' => self::ENTITY_NAMESPACE . '\TestCustomer',
                        'buId' => 8
                    ],
                    [
                        'businessUnitClass' => self::ENTITY_NAMESPACE . '\TestCustomer',
                        'buId' => 12
                    ]
                ]
            ]
        ];
    }

    public function testWarmUpCache(): void
    {
        $cacheTtl = 100000;
        $data = ['cache_ttl' => $cacheTtl];

        $this->ownerTreeMessageFactory
            ->expects(self::once())
            ->method('createMessage')
            ->with($cacheTtl)
            ->willReturn($data);

        $this->messageProducer
            ->expects(self::once())
            ->method('send')
            ->with(Topics::CALCULATE_OWNER_TREE_CACHE, new Message($data));

        $this->treeProvider->setCacheTtl($cacheTtl);
        $this->treeProvider->warmUpCache();
    }
}
