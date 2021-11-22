<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Form\Type;

use Oro\Bundle\FrontendBundle\Form\Type\ThemeSelectType;
use Oro\Component\Layout\Extension\Theme\Model\Theme;
use Oro\Component\Layout\Extension\Theme\Model\ThemeManager;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ThemeSelectTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|ThemeManager */
    private $themeManager;

    /** @var ThemeSelectType */
    private $type;

    protected function setUp(): void
    {
        $this->themeManager = $this->createMock(ThemeManager::class);

        $this->type = new ThemeSelectType($this->themeManager);
    }

    public function testGetParent()
    {
        $this->assertEquals(ChoiceType::class, $this->type->getParent());
    }

    public function testConfigureOptions()
    {
        $themes = [
            $this->getTheme('theme1', 'label1', 'icon1', 'logo1', 'screenshot1', 'description1'),
            $this->getTheme('theme2', 'label2', 'icon2', 'logo2', 'screenshot2', 'description2')
        ];

        $expectedChoices = [
            'label1' => 'theme1',
            'label2' => 'theme2',
        ];

        $this->themeManager->expects($this->once())
            ->method('getEnabledThemes')
            ->with('commerce')
            ->willReturn($themes);

        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with([
                'choices' => $expectedChoices,
            ]);

        $this->type->configureOptions($resolver);
    }

    public function testFinishView()
    {
        $themes = [
            $this->getTheme('theme1', 'label1', 'icon1', 'logo1', 'screenshot1', 'description1'),
            $this->getTheme('theme2', 'label2', 'icon2', 'logo2', 'screenshot2', 'description2')
        ];

        $this->themeManager->expects($this->once())
            ->method('getEnabledThemes')
            ->with('commerce')
            ->willReturn($themes);

        $view = new FormView();
        $form = $this->createMock(FormInterface::class);
        $options = [];

        $this->type->finishView($view, $form, $options);

        $expectedMetadata = [
            'theme1' => [
                'icon' => 'icon1',
                'logo' => 'logo1',
                'screenshot' => 'screenshot1',
                'description' => 'description1'
            ],
            'theme2' => [
                'icon' => 'icon2',
                'logo' => 'logo2',
                'screenshot' => 'screenshot2',
                'description' => 'description2'
            ]
        ];

        $this->assertArrayHasKey('themes-metadata', $view->vars);
        $this->assertEquals($expectedMetadata, $view->vars['themes-metadata']);
    }

    private function getTheme(
        string $name,
        string $label,
        string $icon,
        string $logo,
        string $screenshot,
        string $description
    ): Theme {
        $theme = new Theme($name);
        $theme->setLabel($label);
        $theme->setIcon($icon);
        $theme->setLogo($logo);
        $theme->setScreenshot($screenshot);
        $theme->setDescription($description);

        return $theme;
    }
}
