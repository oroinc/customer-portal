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
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ThemeManager
     */
    protected $themeManager;

    /**
     * @var ThemeSelectType
     */
    protected $type;

    protected function setUp(): void
    {
        $this->themeManager = $this->getMockBuilder('Oro\Component\Layout\Extension\Theme\Model\ThemeManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->type = new ThemeSelectType($this->themeManager);
    }

    protected function tearDown(): void
    {
        unset($this->type, $this->themeManager);
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
            ->will($this->returnValue($themes));

        /** @var \PHPUnit\Framework\MockObject\MockObject|OptionsResolver $resolver */
        $resolver = $this->getMockBuilder('Symfony\Component\OptionsResolver\OptionsResolver')
            ->disableOriginalConstructor()
            ->getMock();
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
            ->will($this->returnValue($themes));

        $view = new FormView();
        /** @var \PHPUnit\Framework\MockObject\MockObject|FormInterface $form */
        $form = $this->createMock('Symfony\Component\Form\FormInterface');
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

    /**
     * @param string $name
     * @param string $label
     * @param string $icon
     * @param string $logo
     * @param string $screenshot
     * @param string $description
     * @return Theme
     */
    protected function getTheme($name, $label, $icon, $logo, $screenshot, $description)
    {
        $theme = new Theme($name);
        $theme->setLabel($label);
        $theme->setIcon($icon);
        $theme->setLogo($logo);
        $theme->setScreenshot($screenshot);
        $theme->setDescription($description);

        return $theme;
    }
}
