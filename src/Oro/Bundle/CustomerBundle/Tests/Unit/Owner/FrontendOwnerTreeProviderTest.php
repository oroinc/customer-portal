<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Owner;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Async\Topic\CustomerCalculateOwnerTreeCacheTopic;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Owner\FrontendOwnerTreeProvider;
use Oro\Bundle\EntityBundle\Tools\DatabaseChecker;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;
use Oro\Bundle\SecurityBundle\Owner\OwnerTree;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\MessageQueue\Client\MessageProducer;
use Oro\Component\Testing\ReflectionUtil;
use Oro\Component\Testing\Unit\ORM\Mocks\ConnectionMock;
use Oro\Component\Testing\Unit\ORM\Mocks\DriverMock;
use Oro\Component\Testing\Unit\ORM\OrmTestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Contracts\Cache\CacheInterface;

class FrontendOwnerTreeProviderTest extends OrmTestCase
{
    /** @var OwnershipMetadataProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $ownershipMetadataProvider;

    /** @var TokenStorageInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenStorage;

    /** @var MessageProducer|\PHPUnit\Framework\MockObject\MockObject */
    private $messageProducer;

    /** @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $logger;

    /** @var FrontendOwnerTreeProvider */
    private $treeProvider;

    protected function setUp(): void
    {
        $conn = new ConnectionMock([], new DriverMock());
        $conn->setDatabasePlatform(new MySqlPlatform());
        $em = $this->getTestEntityManager($conn);
        $em->getConfiguration()->setMetadataDriverImpl(new AnnotationDriver(new AnnotationReader()));

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects(self::any())
            ->method('getManagerForClass')
            ->willReturn($em);

        $databaseChecker = $this->createMock(DatabaseChecker::class);

        $cache = $this->createMock(CacheInterface::class);
        $cache->expects(self::any())
            ->method('get');

        $this->ownershipMetadataProvider = $this->createMock(OwnershipMetadataProviderInterface::class);
        $this->ownershipMetadataProvider->expects(self::any())
            ->method('getUserClass')
            ->willReturn(CustomerUser::class);
        $this->ownershipMetadataProvider->expects(self::any())
            ->method('getBusinessUnitClass')
            ->willReturn(Customer::class);

        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->messageProducer = $this->createMock(MessageProducer::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->treeProvider = new FrontendOwnerTreeProvider(
            $doctrine,
            $databaseChecker,
            $cache,
            $this->ownershipMetadataProvider,
            $this->tokenStorage,
            $this->messageProducer
        );
        $this->treeProvider->setLogger($this->logger);
    }

    public function testSupportsForSupportedUser(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);
        $token->expects(self::atLeastOnce())
            ->method('getUser')
            ->willReturn(new CustomerUser());

        self::assertTrue($this->treeProvider->supports());
    }

    public function testSupportsForNotSupportedUser(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);
        $token->expects(self::once())
            ->method('getUser')
            ->willReturn(new User());

        self::assertFalse($this->treeProvider->supports());
    }

    public function testSupportsWhenNoSecurityToken(): void
    {
        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn(null);

        self::assertFalse($this->treeProvider->supports());
    }

    /**
     * @dataProvider addBusinessUnitDirectCyclicRelationProvider
     */
    public function testDirectCyclicRelationshipBetweenBusinessUnits(
        array $src,
        array $expected,
        array $criticalMessageArguments
    ): void {
        $this->logger->expects(self::once())
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
            self::assertEquals($businessUnits, $tree->getSubordinateBusinessUnitIds($parentBusinessUnit));
        }
    }

    /**
     * @dataProvider addBusinessUnitNotDirectCyclicRelationProvider
     */
    public function testNotDirectCyclicRelationshipBetweenBusinessUnits(
        array $src,
        array $expected,
        array  $criticalMessageArguments
    ): void {
        $this->logger->expects(self::exactly(count($criticalMessageArguments)))
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
            self::assertEquals($businessUnits, $tree->getSubordinateBusinessUnitIds($parentBusinessUnit));
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
                    'businessUnitClass' => Customer::class,
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
                        'businessUnitClass' => Customer::class,
                        'buId' => 5
                    ],
                    [
                        'businessUnitClass' => Customer::class,
                        'buId' => 8
                    ],
                    [
                        'businessUnitClass' => Customer::class,
                        'buId' => 12
                    ]
                ]
            ]
        ];
    }

    public function testWarmUpCache(): void
    {
        $cacheTtl = 100000;

        $this->messageProducer->expects(self::once())
            ->method('send')
            ->with(
                CustomerCalculateOwnerTreeCacheTopic::getName(),
                [CustomerCalculateOwnerTreeCacheTopic::CACHE_TTL => $cacheTtl]
            );

        $this->treeProvider->setCacheTtl($cacheTtl);
        $this->treeProvider->warmUpCache();
    }
}
