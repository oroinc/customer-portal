<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\CMSBundle\Form\Type\WYSIWYGType;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FrontendBundle\Form\Extension\WYSIWYGTypeExtension;
use Oro\Bundle\ThemeBundle\Provider\ThemeConfigurationProvider;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\Layout\Extension\Theme\DataProvider\ThemeProvider;
use Oro\Component\Layout\Extension\Theme\Model\ThemeManager;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\Packages;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WYSIWYGTypeExtensionTest extends TestCase
{
    use EntityTrait;

    private ThemeManager|MockObject $themeManager;

    private ThemeProvider|MockObject $themeProvider;

    private ConfigManager|MockObject $configManager;

    private WebsiteManager|MockObject $websiteManager;

    private Packages|MockObject $packages;

    private ThemeConfigurationProvider|MockObject $themeConfigurationProvider;

    private WYSIWYGTypeExtension $extension;

    protected function setUp(): void
    {
        $this->themeManager = $this->createMock(ThemeManager::class);
        $this->themeProvider = $this->createMock(ThemeProvider::class);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->websiteManager = $this->createMock(WebsiteManager::class);
        $this->packages = $this->createMock(Packages::class);
        $this->themeConfigurationProvider = $this->createMock(ThemeConfigurationProvider::class);

        $this->extension = new WYSIWYGTypeExtension(
            $this->themeManager,
            $this->themeProvider,
            $this->configManager,
            $this->websiteManager,
            $this->packages,
            $this->themeConfigurationProvider
        );
    }

    public function testGetExtendedTypes(): void
    {
        self::assertEquals([WYSIWYGType::class], $this->extension::getExtendedTypes());
    }

    public function testConfigureOptions(): void
    {
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects(self::once())
            ->method('setDefault')
            ->with('page-component', function () {
            })
            ->willReturnSelf();

        $this->extension->configureOptions($resolver);
    }
}
