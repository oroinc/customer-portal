<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\CMSBundle\Form\Type\WYSIWYGType;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FrontendBundle\Form\Extension\WYSIWYGTypeExtension;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\Layout\Extension\Theme\DataProvider\ThemeProvider;
use Oro\Component\Layout\Extension\Theme\Model\Theme;
use Oro\Component\Layout\Extension\Theme\Model\ThemeManager;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WYSIWYGTypeExtensionTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /**
     * @var ThemeManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $themeManager;

    /**
     * @var ThemeProvider|\PHPUnit\Framework\MockObject\MockObject
     */
    private $themeProvider;

    /**
     * @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configManager;

    /**
     * @var WebsiteManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $websiteManager;

    /**
     * @var WYSIWYGTypeExtension
     */
    protected $extension;

    protected function setUp()
    {
        $this->themeManager = $this->createMock(ThemeManager::class);
        $this->themeProvider = $this->createMock(ThemeProvider::class);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->websiteManager = $this->createMock(WebsiteManager::class);

        $this->extension = new WYSIWYGTypeExtension(
            $this->themeManager,
            $this->themeProvider,
            $this->configManager,
            $this->websiteManager
        );
    }

    public function testGetExtendedTypes(): void
    {
        $this->assertEquals([WYSIWYGType::class], $this->extension::getExtendedTypes());
    }

    public function testConfigureOptions()
    {
        $theme1 = new Theme('theme1');
        $theme1->setLabel('label1');
        $theme1->setGroups(['commerce', 'platform']);
        $theme2 = new Theme('theme2');
        $theme2->setLabel('label2');
        $theme2->setGroups(['platform', 'crm']);
        $theme3 = new Theme('theme3');
        $theme3->setLabel('label3');
        $theme3->setGroups(['commerce', 'crm']);
        $this->themeManager->expects($this->once())
            ->method('getAllThemes')
            ->willReturn([$theme1, $theme2, $theme3]);
        $this->themeProvider->expects($this->exactly(2))
            ->method('getStylesOutput')
            ->withConsecutive(
                [$theme1->getName()],
                [$theme3->getName()]
            )->willReturnOnConsecutiveCalls(
                '/path/to/theme1',
                '/path/to/theme3'
            );
        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_frontend.frontend_theme', false, false, null)
            ->willReturn($theme2->getName());

        $expectedOptions = ['page-component' => [
            'options' => [
                'themes' => [
                    [
                        'name' => $theme1->getName(),
                        'label' => $theme1->getLabel(),
                        'stylesheet' => '/path/to/theme1',
                    ],
                    [
                        'name' => $theme3->getName(),
                        'label' => $theme3->getLabel(),
                        'stylesheet' => '/path/to/theme3',
                    ],
                ]
            ]
        ]];

        $optionsResolver = new OptionsResolver();
        $this->extension->configureOptions($optionsResolver);
        $options = $optionsResolver->resolve();

        $this->assertEquals($expectedOptions, $options);
    }
}
