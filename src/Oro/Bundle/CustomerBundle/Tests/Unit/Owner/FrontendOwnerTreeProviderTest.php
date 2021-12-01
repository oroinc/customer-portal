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
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessageProducer;
use Oro\Component\Testing\ReflectionUtil;
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
    private const ENTITY_NAMESPACE = 'Oro\Bundle\CustomerBundle\Tests\Unit\Owner\Fixtures\Entity';

    /** @var EntityManagerMock */
    private $em;

    /** @var \PHPUnit\Framework\MockObject\MockObject|DatabaseChecker */
    private $databaseChecker;

    /** @var \PHPUnit\Framework\MockObject\MockObject|CacheProvider */
    private $cache;

    /** @var \PHPUnit\Framework\MockObject\MockObject|OwnershipMetadataProviderInterface */
    private $ownershipMetadataProvider;

    /** @var \PHPUnit\Framework\MockObject\MockObject|TokenStorageInterface */
    private $tokenStorage;

    /** @var \PHPUnit\Framework\MockObject\MockObject|MessageProducer */
    private $messageProducer;

    /** @var \PHPUnit\Framework\MockObject\MockObject|OwnerTreeMessageFactory */
    private $ownerTreeMessageFactory;

    /** @var FrontendOwnerTreeProvider */
    private $treeProvider;

    /** @var LoggerInterface */
    private $logger;

    protected function setUp(): void
    {
        $conn = new ConnectionMock([], new DriverMock());
        $conn->setDatabasePlatform(new MySqlPlatform());
        $this->em = $this->getTestEntityManager($conn);
        $this->em->getConfiguration()->setMetadataDriverImpl(new AnnotationDriver(
            new AnnotationReader(),
            self::ENTITY_NAMESPACE
        ));
        $this->em->getConfiguration()->setEntityNamespaces([
            'Test' => self::ENTITY_NAMESPACE
        ]);

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->any())
            ->method('getManagerForClass')
            ->willReturn($this->em);

        $this->databaseChecker = $this->createMock(DatabaseChecker::class);

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
            ->willReturn(false);
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
    public function testDirectCyclicRelationshipBetweenBusinessUnits(
        array $src,
        array $expected,
        array $criticalMessageArguments
    ) {
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
        $subordinateBusinessUnitIds = ReflectionUtil::callMethod(
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
    public function testNotDirectCyclicRelationshipBetweenBusinessUnits(
        array $src,
        array $expected,
        array  $criticalMessageArguments
    ) {
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
        $subordinateBusinessUnitIds = ReflectionUtil::callMethod(
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

    public function addBusinessUnitDirectCyclicRelationProvider(): array
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

    public function addBusinessUnitNotDirectCyclicRelationProvider(): array
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

        $this->ownerTreeMessageFactory->expects(self::once())
            ->method('createMessage')
            ->with($cacheTtl)
            ->willReturn($data);

        $this->messageProducer->expects(self::once())
            ->method('send')
            ->with(Topics::CALCULATE_OWNER_TREE_CACHE, new Message($data));

        $this->treeProvider->setCacheTtl($cacheTtl);
        $this->treeProvider->warmUpCache();
    }
}
