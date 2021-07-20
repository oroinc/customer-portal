<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Owner\Metadata;

use Doctrine\Common\Cache\CacheProvider;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Owner\Metadata\FrontendOwnershipMetadata;
use Oro\Bundle\CustomerBundle\Owner\Metadata\FrontendOwnershipMetadataProvider;
use Oro\Bundle\EntityBundle\ORM\EntityClassResolver;
use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class FrontendOwnershipMetadataProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|ConfigManager */
    protected $configManager;

    /** @var \PHPUnit\Framework\MockObject\MockObject|EntityClassResolver */
    protected $entityClassResolver;

    /** @var \PHPUnit\Framework\MockObject\MockObject|TokenAccessorInterface */
    protected $tokenAccessor;

    /** @var \PHPUnit\Framework\MockObject\MockObject|CacheProvider */
    protected $cache;

    /** @var FrontendOwnershipMetadataProvider */
    protected $provider;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ContainerInterface
     */
    protected $container;

    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->entityClassResolver = $this->createMock(EntityClassResolver::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $this->cache = $this->createMock(CacheProvider::class);

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

    public function testGetUserClass()
    {
        $this->entityClassResolver->expects($this->exactly(2))
            ->method('getEntityClass')
            ->willReturnMap([
                ['AcmeBundle:CustomerUser', 'AcmeBundle\Entity\CustomerUser'],
                ['AcmeBundle:Customer', 'AcmeBundle\Entity\Customer'],
            ]);

        $this->assertEquals('AcmeBundle\Entity\CustomerUser', $this->provider->getUserClass());
        // test that the class is cached in a local property
        $this->assertEquals('AcmeBundle\Entity\CustomerUser', $this->provider->getUserClass());
    }

    public function testGetBusinessUnitClass()
    {
        $this->entityClassResolver->expects($this->exactly(2))
            ->method('getEntityClass')
            ->willReturnMap([
                ['AcmeBundle:CustomerUser', 'AcmeBundle\Entity\CustomerUser'],
                ['AcmeBundle:Customer', 'AcmeBundle\Entity\Customer'],
            ]);

        $this->assertEquals('AcmeBundle\Entity\Customer', $this->provider->getBusinessUnitClass());
        // test that the class is cached in a local property
        $this->assertEquals('AcmeBundle\Entity\Customer', $this->provider->getBusinessUnitClass());
    }

    public function testGetOrganizationClass()
    {
        $this->entityClassResolver->expects($this->never())
            ->method('getEntityClass');

        $this->assertNull($this->provider->getOrganizationClass());
    }

    public function testGetMetadataWithoutCache()
    {
        $config = new Config(new EntityConfigId('ownership', \stdClass::class));
        $config
            ->set('frontend_owner_type', 'USER')
            ->set('frontend_owner_field_name', 'test_field')
            ->set('frontend_owner_column_name', 'test_column')
            ->set('frontend_customer_field_name', 'customer')
            ->set('frontend_customer_column_name', 'customer_id');

        $this->configManager->expects($this->once())
            ->method('hasConfig')
            ->with(\stdClass::class)
            ->willReturn(true);
        $this->configManager->expects($this->once())
            ->method('getEntityConfig')
            ->with('ownership', \stdClass::class)
            ->willReturn($config);

        $this->cache = null;

        $this->assertEquals(
            new FrontendOwnershipMetadata('USER', 'test_field', 'test_column', '', '', 'customer', 'customer_id'),
            $this->provider->getMetadata(\stdClass::class)
        );
    }

    public function testGetMetadataUndefinedClassWithCache()
    {
        $this->configManager->expects($this->once())
            ->method('hasConfig')
            ->with('UndefinedClass')
            ->willReturn(false);
        $this->configManager->expects($this->never())
            ->method('getEntityConfig');

        $this->cache->expects($this->at(0))
            ->method('fetch')
            ->with('UndefinedClass')
            ->willReturn(false);
        $this->cache->expects($this->at(2))
            ->method('fetch')
            ->with('UndefinedClass')
            ->willReturn(true);
        $this->cache->expects($this->once())
            ->method('save')
            ->with('UndefinedClass', true);

        $metadata = new FrontendOwnershipMetadata();
        $providerWithCleanCache = clone $this->provider;

        // no cache
        $this->assertEquals($metadata, $this->provider->getMetadata('UndefinedClass'));

        // local cache
        $this->assertEquals($metadata, $this->provider->getMetadata('UndefinedClass'));

        // cache
        $this->assertEquals($metadata, $providerWithCleanCache->getMetadata('UndefinedClass'));
    }

    /**
     * @dataProvider supportsDataProvider
     *
     * @param object|null $user
     * @param bool $expectedResult
     */
    public function testSupports($user, $expectedResult)
    {
        $this->tokenAccessor->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->assertEquals($expectedResult, $this->provider->supports());
    }

    /**
     * @return array
     */
    public function supportsDataProvider()
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

    /**
     * @dataProvider owningEntityNamesDataProvider
     */
    public function testInvalidOwningEntityNames(array $owningEntityNames)
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

    /**
     * @return array
     */
    public function owningEntityNamesDataProvider()
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
     * @param int $maxAccessLevel
     * @param int $accessLevel
     * @param string|null $className
     * @param bool|null $hasOwner
     * @dataProvider getMaxAccessLevelDataProvider
     */
    public function testGetMaxAccessLevel($maxAccessLevel, $accessLevel, $className = null, $hasOwner = null)
    {
        if (null !== $hasOwner) {
            if ($hasOwner) {
                $metadata = new FrontendOwnershipMetadata('FRONTEND_USER', 'owner', 'owner_id');
            } else {
                $metadata = new FrontendOwnershipMetadata();
            }

            $this->cache->expects($this->any())
                ->method('fetch')
                ->with($className)
                ->willReturn($metadata);
        }

        $this->assertEquals($maxAccessLevel, $this->provider->getMaxAccessLevel($accessLevel, $className));
    }

    /**
     * @return array
     */
    public function getMaxAccessLevelDataProvider()
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

    public function testWarmUpCache()
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
        $this->configManager->expects($this->once())
            ->method('getConfigs')
            ->with('ownership')
            ->willReturn([$config1, $config2]);

        $this->configManager->expects($this->atLeastOnce())
            ->method('hasConfig')
            ->willReturn(true);
        $this->configManager->expects($this->atLeastOnce())
            ->method('getEntityConfig')
            ->willReturnMap($configMap);

        $this->cache->expects($this->once())
            ->method('fetch')
            ->with($this->equalTo('AcmeBundle\Entity\Customer'));

        $this->provider->warmUpCache();
    }
}
