<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Layout\Extension;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;
use Oro\Bundle\LayoutBundle\Event\LayoutContextChangedEvent;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\ThemeBundle\Entity\ThemeConfiguration;
use Symfony\Component\EventDispatcher\EventDispatcher;

class SvgIconsSupportContextConfiguratorTest extends WebTestCase
{
    use ConfigManagerAwareTestTrait;

    private int $originalThemeConfig;

    #[\Override]
    protected function setUp(): void
    {
        $this->initClient();

        $this->originalThemeConfig = self::getConfigManager()->get('oro_theme.theme_configuration');
    }

    #[\Override]
    protected function tearDown(): void
    {
        self::getConfigManager()->set('oro_theme.theme_configuration', $this->originalThemeConfig);
        self::getConfigManager()->flush();
    }

    private function getThemeConfigurationEntityManager(): EntityManagerInterface
    {
        return self::getContainer()->get('doctrine')->getManagerForClass(ThemeConfiguration::class);
    }

    public function testIsSvgIconsSupportedOnDefaultTheme(): void
    {
        /** @var EventDispatcher $eventDispatcher */
        $eventDispatcher = self::getContainer()->get('event_dispatcher');
        $eventDispatcher->addListener(LayoutContextChangedEvent::class, [$this, 'assertSvgIconsSupportIsTrue']);

        $this->client->request('GET', $this->getUrl('oro_frontend_root'));
    }

    public function testIsSvgIconsSupportedOnCustomThemeThatInheritsDefault(): void
    {
        $themeConfig = (new ThemeConfiguration())
            ->setName('Custom')
            ->setTheme('custom');

        $entityManager = $this->getThemeConfigurationEntityManager();
        $entityManager->persist($themeConfig);
        $entityManager->flush();

        self::getConfigManager()->set('oro_theme.theme_configuration', $themeConfig->getId());
        self::getConfigManager()->flush();

        /** @var EventDispatcher $eventDispatcher */
        $eventDispatcher = self::getContainer()->get('event_dispatcher');
        $eventDispatcher->addListener(LayoutContextChangedEvent::class, [$this, 'assertSvgIconsSupportIsTrue']);

        $this->client->request('GET', $this->getUrl('oro_frontend_root'));
    }

    public function assertSvgIconsSupportIsTrue(LayoutContextChangedEvent $event): void
    {
        try {
            self::assertTrue($event->getCurrentContext()->getOr('is_svg_icons_support'));
        } finally {
            self::getContainer()->get('event_dispatcher')->removeListener(
                LayoutContextChangedEvent::class,
                [$this, 'assertSvgIconsSupportIsTrue']
            );
        }
    }

    public function testIsSvgIconsSupportedOnCustomStandaloneThemeThatNotInheritsDefault(): void
    {
        $themeConfig = (new ThemeConfiguration())
            ->setName('Custom Standalone')
            ->setTheme('custom_standalone');

        $entityManager = $this->getThemeConfigurationEntityManager();
        $entityManager->persist($themeConfig);
        $entityManager->flush();

        self::getConfigManager()->set('oro_theme.theme_configuration', $themeConfig->getId());
        self::getConfigManager()->flush();

        /** @var EventDispatcher $eventDispatcher */
        $eventDispatcher = self::getContainer()->get('event_dispatcher');
        $eventDispatcher->addListener(LayoutContextChangedEvent::class, [$this, 'assertSvgIconsSupportIsFalse']);

        $this->client->request('GET', $this->getUrl('oro_frontend_root'));
    }

    public function assertSvgIconsSupportIsFalse(LayoutContextChangedEvent $event): void
    {
        try {
            self::assertFalse($event->getCurrentContext()->getOr('is_svg_icons_support'));
        } finally {
            self::getContainer()->get('event_dispatcher')->removeListener(
                LayoutContextChangedEvent::class,
                [$this, 'assertSvgIconsSupportIsFalse']
            );
        }
    }
}
