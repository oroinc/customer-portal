<?php

declare(strict_types=1);

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Form\Extension;

use Knp\Menu\ItemInterface;
use Oro\Bundle\CommerceMenuBundle\Form\Extension\MenuUpdateWarningExtension;
use Oro\Bundle\NavigationBundle\Form\Type\MenuUpdateType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormView;

class MenuUpdateWarningExtensionTest extends TestCase
{
    public function testGetExtendedTypes()
    {
        static::assertContains(MenuUpdateType::class, MenuUpdateWarningExtension::getExtendedTypes());
    }

    public function testBuildViewForMenuWithoutWarning()
    {
        $extension = new MenuUpdateWarningExtension();

        $menu = $this->createMock(ItemInterface::class);
        $menu->method('getExtra')->willReturn(null);

        $formConfig = $this->createMock(FormConfigInterface::class);
        $formConfig->method('getOption')->willReturn($menu);

        $form = $this->createMock(Form::class);
        $form->method('getConfig')->willReturn($formConfig);

        $formView = new FormView();

        $extension->buildView($formView, $form, []);
        static::assertArrayNotHasKey('warning', $formView->vars);
    }

    public function testBuildViewForMenuWithWarning()
    {
        $extension = new MenuUpdateWarningExtension();

        $warningText = 'Test Warning';

        $menu = $this->createMock(ItemInterface::class);
        $menu->method('getExtra')->willReturn($warningText);

        $formConfig = $this->createMock(FormConfigInterface::class);
        $formConfig->method('getOption')->willReturn($menu);

        $form = $this->createMock(Form::class);
        $form->method('getConfig')->willReturn($formConfig);

        $formView = new FormView();

        $extension->buildView($formView, $form, []);
        static::assertEquals($warningText, $formView->vars['warning']);
    }
}
