<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Acl\Voter;

use Oro\Bundle\CMSBundle\Tests\Functional\DataFixtures\LoadTextContentVariantsData;
use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;
use Oro\Bundle\FrontendBundle\DependencyInjection\Configuration;
use Oro\Bundle\SecurityBundle\Acl\BasicPermission;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @dbIsolationPerTest
 */
class ContentBlockVoterTest extends WebTestCase
{
    use ConfigManagerAwareTestTrait;

    private AuthorizationCheckerInterface $checker;

    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->loadFixtures([
            LoadTextContentVariantsData::class,
        ]);
        $this->checker = $this->getClientContainer()->get('security.authorization_checker');
    }

    protected function tearDown(): void
    {
        $config = self::getConfigManager();
        $config->reset(Configuration::getConfigKeyByName(Configuration::PROMOTIONAL_CONTENT));
        parent::tearDown();
    }

    public function testDeniedIfUsedForGlobalConfiguration(): void
    {
        $value = $this->getReference('content_block_1');

        $config = self::getConfigManager();
        $config->set(Configuration::getConfigKeyByName(Configuration::PROMOTIONAL_CONTENT), $value);
        $config->flush();

        $this->login(self::AUTH_USER, self::AUTH_PW);

        self::assertFalse($this->checker->isGranted(BasicPermission::DELETE, $value));
    }

    public function testDeniedIfUsedForOrganizationConfiguration(): void
    {
        $value = $this->getReference('content_block_1');

        $config = self::getConfigManager();
        $config->set(
            Configuration::getConfigKeyByName(Configuration::PROMOTIONAL_CONTENT),
            $value,
            $this->getReference(LoadOrganization::ORGANIZATION)
        );
        $config->flush();

        $this->login(self::AUTH_USER, self::AUTH_PW);

        self::assertFalse($this->checker->isGranted(BasicPermission::DELETE, $value));
    }

    public function testAbstainOnEmpty(): void
    {
        $value = $this->getReference('content_block_1');

        $config = self::getConfigManager();
        $config->set(Configuration::getConfigKeyByName(Configuration::PROMOTIONAL_CONTENT), null);
        $config->flush();

        $this->login(self::AUTH_USER, self::AUTH_PW);

        self::assertTrue($this->checker->isGranted(BasicPermission::DELETE, $value));
    }

    public function testAbstainOnNotSelectedAtConfiguration(): void
    {
        $config = self::getConfigManager();
        $config->set(
            Configuration::getConfigKeyByName(Configuration::PROMOTIONAL_CONTENT),
            $this->getReference('content_block_2')
        );
        $config->flush();

        $this->login(self::AUTH_USER, self::AUTH_PW);

        self::assertTrue($this->checker->isGranted(BasicPermission::DELETE, $this->getReference('content_block_1')));
    }

    private function login(string $email, string $password): void
    {
        $this->initClient([], $this->generateBasicAuthHeader($email, $password));
        $this->client->useHashNavigation(true);
        $this->client->request('GET', '/admin'); // any page to apply new user
    }
}
