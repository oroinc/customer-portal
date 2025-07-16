<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Unit\EmailTemplateCandidates;

use Oro\Bundle\EmailBundle\Model\EmailTemplateCriteria;
use Oro\Bundle\FrontendBundle\EmailTemplateCandidates\LayoutThemeAwareEmailTemplateCandidatesProvider;
use Oro\Bundle\ThemeBundle\Provider\ThemeConfigurationProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LayoutThemeAwareEmailTemplateCandidatesProviderTest extends TestCase
{
    private ThemeConfigurationProvider&MockObject $themeConfigurationProvider;
    private LayoutThemeAwareEmailTemplateCandidatesProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->themeConfigurationProvider = $this->createMock(ThemeConfigurationProvider::class);

        $this->provider = new LayoutThemeAwareEmailTemplateCandidatesProvider($this->themeConfigurationProvider);
    }

    public function testShouldReturnEmptyArrayWhenStartsWithAt(): void
    {
        self::assertEmpty(
            $this->provider->getCandidatesNames(new EmailTemplateCriteria('@sample_namespace/sample_name.html.twig'))
        );
    }

    public function testShouldReturnEmptyArrayWhenNoThemeName(): void
    {
        $this->themeConfigurationProvider->expects(self::once())
            ->method('getThemeName')
            ->willReturn(null);

        self::assertEmpty($this->provider->getCandidatesNames(new EmailTemplateCriteria('sample_name')));
    }

    public function testWhenHasThemeName(): void
    {
        $themeName = 'sample_theme';
        $this->themeConfigurationProvider->expects(self::once())
            ->method('getThemeName')
            ->willReturn($themeName);

        self::assertEquals(
            ['@theme:name=' . $themeName . '/sample_name.html.twig'],
            $this->provider->getCandidatesNames(new EmailTemplateCriteria('sample_name'))
        );
    }
}
