<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Owner\Metadata;

use Oro\Bundle\CacheBundle\Generator\UniversalCacheKeyGenerator;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Owner\Metadata\FrontendOwnershipMetadata;
use Oro\Bundle\CustomerBundle\Owner\Metadata\FrontendOwnershipMetadataProvider;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\EntityBundle\ORM\EntityClassResolver;
use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class FrontendOwnershipMetadataProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject */
    private $configManager;

    /** @var EntityClassResolver|\PHPUnit\Framework\MockObject\MockObject */
    private $entityClassResolver;

    /** @var TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenAccessor;

    /** @var CacheInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $cache;

    /** @var FrontendOwnershipMetadataProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->entityClassResolver = $this->createMock(EntityClassResolver::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $this->cache = $this->createMock(CacheInterface::class);

        $this->provider = new FrontendOwnershipMetadataProvider(
            [
                'business_unit' => 'AcmeBundle:Customer',
                'user' => 'AcmeBundle:CustomerUser',
            ],
            $this->configManager,
            $this->entityClassResolver,
            $this->tokenAccessor,
            $this->cache
        );
    }

    public function testGetUserClass(): void
    {
        $this->entityClassResolver->expects(self::exactly(2))
            ->method('getEntityClass')
            ->willReturnMap([
                ['AcmeBundle:CustomerUser', 'AcmeBundle\Entity\CustomerUser'],
                ['AcmeBundle:Customer', 'AcmeBundle\Entity\Customer'],
            ]);

        self::assertEquals('AcmeBundle\Entity\CustomerUser', $this->provider->getUserClass());
        // test that the class is cached in a local property
        self::assertEquals('AcmeBundle\Entity\CustomerUser', $this->provider->getUserClass());
    }

    public function testGetBusinessUnitClass(): void
    {
        $this->entityClassResolver->expects(self::exactly(2))
            ->method('getEntityClass')
            ->willReturnMap([
                ['AcmeBundle:CustomerUser', 'AcmeBundle\Entity\CustomerUser'],
                ['AcmeBundle:Customer', 'AcmeBundle\Entity\Customer'],
            ]);

        self::assertEquals('AcmeBundle\Entity\Customer', $this->provider->getBusinessUnitClass());
        // test that the class is cached in a local property
        self::assertEquals('AcmeBundle\Entity\Customer', $this->provider->getBusinessUnitClass());
    }

    public function testGetOrganizationClass(): void
    {
        $this->entityClassResolver->expects(self::never())
            ->method('getEntityClass');

        self::assertNull($this->provider->getOrganizationClass());
    }

    public function testGetMetadataWithoutCache(): void
    {
        $config = new Config(new EntityConfigId('ownership', \stdClass::class));
        $config
            ->set('frontend_owner_type', 'FRONTEND_USER')
            ->set('frontend_owner_field_name', 'test_field')
            ->set('frontend_owner_column_name', 'test_column')
            ->set('frontend_customer_field_name', 'customer')
            ->set('frontend_customer_column_name', 'customer_id');

        $this->configManager->expects(self::once())
            ->method('hasConfig')
            ->with(\stdClass::class)
            ->willReturn(true);
        $this->configManager->expects(self::once())
            ->method('getEntityConfig')
            ->with('ownership', \stdClass::class)
            ->willReturn($config);

        $this->cache->expects(self::once())
            ->method('get')
            ->willReturnCallback(function ($cacheKey, $callback) {
                return $callback($this->createMock(ItemInterface::class));
            });

        self::assertEquals(
            new FrontendOwnershipMetadata(
                'FRONTEND_USER',
                'test_field',
                'test_column',
                '',
                '',
                'customer',
                'customer_id'
            ),
            $this->provider->getMetadata(\stdClass::class)
        );
    }

    public function testGetMetadataUndefinedClassWithCache(): void
    {
        $this->configManager->expects(self::never())
            ->method('hasConfig')
            ->with('UndefinedClass')
            ->willReturn(false);
        $this->configManager->expects(self::never())
            ->method('getEntityConfig');

        $this->cache->expects(self::exactly(2))
            ->method('get')
            ->with('UndefinedClass')
            ->willReturn(true);

        $metadata = new FrontendOwnershipMetadata();
        $providerWithCleanCache = clone $this->provider;

        // no cache
        self::assertEquals($metadata, $this->provider->getMetadata('UndefinedClass'));
        // local cache
        self::assertEquals($metadata, $this->provider->getMetadata('UndefinedClass'));
        // cache
        self::assertEquals($metadata, $providerWithCleanCache->getMetadata('UndefinedClass'));
    }

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupports(?object $user, bool $expectedResult): void
    {
        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $this->tokenAccessor->expects(self::any())
            ->method('getToken')
            ->willReturn(new \stdClass());

        self::assertEquals($expectedResult, $this->provider->supports());
    }

    public function supportsDataProvider(): array
    {
        return [
            'incorrect user object' => [
                'user' => new \stdClass(),
                'expectedResult' => false,
            ],
            'customer user' => [
                'user' => new CustomerUser(),
                'expectedResult' => true,
            ],
            'user is not logged in' => [
                'user' => null,
                'expectedResult' => false,
            ],
        ];
    }

    public function testSupportsWithAnonymousCustomerUserToken(): void
    {
        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn(null);

        $this->tokenAccessor->expects(self::any())
            ->method('getToken')
            ->willReturn(new AnonymousCustomerUserToken(''));

        self::assertTrue($this->provider->supports());
    }

    /**
     * @dataProvider owningEntityNamesDataProvider
     */
    public function testInvalidOwningEntityNames(array $owningEntityNames): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The $owningEntityNames must contains "business_unit" and "user" keys.');

        $provider = new FrontendOwnershipMetadataProvider(
            $owningEntityNames,
            $this->configManager,
            $this->entityClassResolver,
            $this->tokenAccessor,
            $this->cache
        );
        $provider->getUserClass();
    }

    public function owningEntityNamesDataProvider(): array
    {
        return [
            [
                'owningEntityNames' => [],
            ],
            [
                'owningEntityNames' => [
                    'business_unit' => 'AcmeBundle\Entity\Customer',
                ],
            ],
            [
                'owningEntityNames' => [
                    'user' => 'AcmeBundle\Entity\User',
                ],
            ],
        ];
    }

    /**
     * @dataProvider getMaxAccessLevelDataProvider
     */
    public function testGetMaxAccessLevel(
        int $maxAccessLevel,
        int $accessLevel,
        ?string $className = null,
        ?bool $hasOwner = null
    ): void {
        if (null !== $hasOwner) {
            if ($hasOwner) {
                $metadata = new FrontendOwnershipMetadata('FRONTEND_USER', 'owner', 'owner_id');
            } else {
                $metadata = new FrontendOwnershipMetadata();
            }

            $this->cache->expects(self::any())
                ->method('get')
                ->with($className)
                ->willReturn($metadata);
        }

        self::assertEquals($maxAccessLevel, $this->provider->getMaxAccessLevel($accessLevel, $className));
    }

    public function getMaxAccessLevelDataProvider(): array
    {
        return [
            'without class' => [
                'maxAccessLevel' => AccessLevel::DEEP_LEVEL,
                'accessLevel' => AccessLevel::SYSTEM_LEVEL,
            ],
            'NONE default' => [
                'maxAccessLevel' => AccessLevel::NONE_LEVEL,
                'accessLevel' => AccessLevel::NONE_LEVEL,
                'className' => \stdClass::class,
            ],
            'BASIC default' => [
                'maxAccessLevel' => AccessLevel::BASIC_LEVEL,
                'accessLevel' => AccessLevel::BASIC_LEVEL,
                'className' => \stdClass::class,
            ],
            'LOCAL default' => [
                'maxAccessLevel' => AccessLevel::LOCAL_LEVEL,
                'accessLevel' => AccessLevel::LOCAL_LEVEL,
                'className' => \stdClass::class,
            ],
            'DEEP default' => [
                'maxAccessLevel' => AccessLevel::DEEP_LEVEL,
                'accessLevel' => AccessLevel::DEEP_LEVEL,
                'className' => \stdClass::class,
            ],
            'not allowed with owner' => [
                'maxAccessLevel' => AccessLevel::DEEP_LEVEL,
                'accessLevel' => AccessLevel::SYSTEM_LEVEL,
                'className' => \stdClass::class,
                'hasOwner' => true,
            ],
            'not allowed without owner' => [
                'maxAccessLevel' => AccessLevel::GLOBAL_LEVEL,
                'accessLevel' => AccessLevel::GLOBAL_LEVEL,
                'className' => \stdClass::class,
                'hasOwner' => false,
            ],

        ];
    }

    public function testWarmUpCache(): void
    {
        $config1 = new Config(new EntityConfigId('ownership', 'AcmeBundle\Entity\CustomerUser'));
        $config2 = new Config(new EntityConfigId('ownership', 'AcmeBundle\Entity\Customer'));

        $securityConfig1 = new Config(new EntityConfigId('security', 'AcmeBundle\Entity\CustomerUser'));
        $securityConfig2 = new Config(new EntityConfigId('security', 'AcmeBundle\Entity\Customer'));
        $securityConfig2->set('group_name', 'commerce');

        $configMap = [
            ['ownership', 'AcmeBundle\Entity\CustomerUser', $config1],
            ['ownership', 'AcmeBundle\Entity\Customer', $config2],
            ['security', 'AcmeBundle\Entity\CustomerUser', $securityConfig1],
            ['security', 'AcmeBundle\Entity\Customer', $securityConfig2],
        ];
        $this->configManager->expects(self::once())
            ->method('getConfigs')
            ->with('ownership')
            ->willReturn([$config1, $config2]);

        $this->configManager->expects(self::atLeastOnce())
            ->method('hasConfig')
            ->willReturn(true);
        $this->configManager->expects(self::atLeastOnce())
            ->method('getEntityConfig')
            ->willReturnMap($configMap);

        $this->cache->expects(self::once())
            ->method('get')
            ->with(UniversalCacheKeyGenerator::normalizeCacheKey('AcmeBundle\Entity\Customer'));

        $this->provider->warmUpCache();
    }
}
