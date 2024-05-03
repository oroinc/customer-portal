<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Acl\Voter;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CMSBundle\Tests\Functional\DataFixtures\LoadTextContentVariantsData;
use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;
use Oro\Bundle\LayoutBundle\Layout\Extension\ThemeConfiguration as LayoutThemeConfiguration;
use Oro\Bundle\SecurityBundle\Acl\BasicPermission;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\ThemeBundle\DependencyInjection\Configuration;
use Oro\Bundle\ThemeBundle\Entity\ThemeConfiguration;
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

    public function testDeniedIfUsedForGlobalThemeConfiguration(): void
    {
        $contentBlock = $this->getReference('content_block_1');

        $value = self::getConfigManager()->get(Configuration::getConfigKeyByName(Configuration::THEME_CONFIGURATION));
        $themeConfiguration = self::getDoctrine()->getRepository(ThemeConfiguration::class)->find($value);
        $themeConfiguration->addConfigurationOption(
            LayoutThemeConfiguration::buildOptionKey('header', 'promotional_content'),
            $contentBlock->getId()
        );

        $this->login(self::AUTH_USER, self::AUTH_PW);

        self::assertFalse($this->checker->isGranted(BasicPermission::DELETE, $contentBlock));
    }

    public function testAbstainOnEmpty(): void
    {
        $contentBlock = $this->getReference('content_block_1');

        $this->login(self::AUTH_USER, self::AUTH_PW);

        self::assertTrue($this->checker->isGranted(BasicPermission::DELETE, $contentBlock));
    }

    public function testAbstainOnNotSelectedAtConfiguration(): void
    {
        $this->login(self::AUTH_USER, self::AUTH_PW);

        self::assertTrue($this->checker->isGranted(BasicPermission::DELETE, $this->getReference('content_block_2')));
    }

    private function login(string $email, string $password): void
    {
        $this->initClient([], $this->generateBasicAuthHeader($email, $password));
        $this->client->useHashNavigation(true);
        $this->client->request('GET', '/admin'); // any page to apply new user
    }

    private static function getDoctrine(): ManagerRegistry
    {
        return self::getContainer()->get('doctrine');
    }
}
