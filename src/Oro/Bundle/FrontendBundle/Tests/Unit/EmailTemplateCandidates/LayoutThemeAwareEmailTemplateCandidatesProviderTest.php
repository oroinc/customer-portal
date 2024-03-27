<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Unit\EmailTemplateCandidates;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EmailBundle\Model\EmailTemplateCriteria;
use Oro\Bundle\FrontendBundle\EmailTemplateCandidates\LayoutThemeAwareEmailTemplateCandidatesProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LayoutThemeAwareEmailTemplateCandidatesProviderTest extends TestCase
{
    private ConfigManager|MockObject $configManager;

    private LayoutThemeAwareEmailTemplateCandidatesProvider $provider;

    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);

        $this->provider = new LayoutThemeAwareEmailTemplateCandidatesProvider($this->configManager);
    }

    public function testShouldReturnEmptyArrayWhenStartsWithAt(): void
    {
        self::assertEmpty(
            $this->provider->getCandidatesNames(new EmailTemplateCriteria('@sample_namespace/sample_name.html.twig'))
        );
    }

    public function testShouldReturnEmptyArrayWhenNoThemeName(): void
    {
        $this->configManager
            ->expects(self::once())
            ->method('get')
            ->with('oro_frontend.frontend_theme')
            ->willReturn(null);

        self::assertEmpty($this->provider->getCandidatesNames(new EmailTemplateCriteria('sample_name')));
    }

    public function testWhenHasThemeName(): void
    {
        $themeName = 'sample_theme';
        $this->configManager
            ->expects(self::once())
            ->method('get')
            ->with('oro_frontend.frontend_theme')
            ->willReturn($themeName);

        self::assertEquals(
            ['@theme:name=' . $themeName . '/sample_name.html.twig'],
            $this->provider->getCandidatesNames(new EmailTemplateCriteria('sample_name'))
        );
    }
}
