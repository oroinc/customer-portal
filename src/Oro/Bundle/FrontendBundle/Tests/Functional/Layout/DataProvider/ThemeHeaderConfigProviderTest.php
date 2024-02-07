<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Layout\DataProvider;

use Oro\Bundle\CMSBundle\Tests\Functional\DataFixtures\LoadTextContentVariantsData;
use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUser;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerVisitors;
use Oro\Bundle\FrontendBundle\DependencyInjection\Configuration;
use Oro\Bundle\FrontendBundle\Layout\DataProvider\ThemeHeaderConfigProvider;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;

/**
 * @dbIsolationPerTest
 */
class ThemeHeaderConfigProviderTest extends WebTestCase
{
    use ConfigManagerAwareTestTrait;

    private ThemeHeaderConfigProvider $provider;

    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([
            LoadTextContentVariantsData::class,
            LoadCustomerUser::class,
            LoadCustomerVisitors::class,
        ]);
        $this->provider = $this->getClientContainer()->get(ThemeHeaderConfigProvider::class);
    }

    protected function tearDown(): void
    {
        $config = self::getConfigManager();
        $config->reset(Configuration::getConfigKeyByName(Configuration::PROMOTIONAL_CONTENT));
        $this->getContainer()->get('security.token_storage')->setToken(null);
        parent::tearDown();
    }

    public function testGetPromotionalBlockAliasForVisitors(): void
    {
        /** @var CustomerVisitor $visitor */
        $visitor = $this->getReference(LoadCustomerVisitors::CUSTOMER_VISITOR);
        $this->getContainer()
            ->get('security.token_storage')
            ->setToken(new AnonymousCustomerUserToken(
                $visitor,
                [],
                $this->getReference(LoadOrganization::ORGANIZATION)
            ));

        $config = self::getConfigManager();
        $config->set(
            Configuration::getConfigKeyByName(Configuration::PROMOTIONAL_CONTENT),
            $this->getReference('content_block_1')->getId()
        );
        $config->flush();
        self::assertEquals('content_block_1', $this->provider->getPromotionalBlockAlias());
    }

    public function testGetPromotionalBlockAliasForCustomerUser(): void
    {
        /** @var CustomerUser $user */
        $user = $this->getReference(LoadCustomerUser::CUSTOMER_USER);
        $this->getContainer()
            ->get('security.token_storage')
            ->setToken(new UsernamePasswordOrganizationToken(
                $user,
                'k',
                $user->getOrganization(),
                $user->getUserRoles()
            ));

        $config = self::getConfigManager();
        $config->set(
            Configuration::getConfigKeyByName(Configuration::PROMOTIONAL_CONTENT),
            $this->getReference('content_block_1')->getId(),
        );
        $config->flush();
        self::assertEquals('content_block_1', $this->provider->getPromotionalBlockAlias());
    }

    public function testGetPromotionalBlockAliasForAnonymous(): void
    {
        $this->getContainer()
            ->get('security.token_storage')
            ->setToken(null);

        $config = self::getConfigManager();
        $config->set(
            Configuration::getConfigKeyByName(Configuration::PROMOTIONAL_CONTENT),
            $this->getReference('content_block_1')->getId(),
        );
        $config->flush();
        self::assertEquals('content_block_1', $this->provider->getPromotionalBlockAlias());
    }
}
