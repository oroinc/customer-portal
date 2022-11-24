<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\CustomerBundle\Form\Extension\OroEntitySelectOrCreateInlineExtension;
use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OroEntitySelectOrCreateInlineExtensionTest extends \PHPUnit\Framework\TestCase
{
    /** @var FrontendHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $frontendHelper;

    /** @var OroEntitySelectOrCreateInlineExtension */
    private $extension;

    protected function setUp(): void
    {
        $this->frontendHelper = $this->createMock(FrontendHelper::class);

        $this->extension = new OroEntitySelectOrCreateInlineExtension($this->frontendHelper);
    }

    public function testGetExtendedTypes()
    {
        $this->assertEquals(
            [OroEntitySelectOrCreateInlineType::class],
            OroEntitySelectOrCreateInlineExtension::getExtendedTypes()
        );
    }

    public function testConfigureOptionsForBackend()
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->never())
            ->method($this->anything());

        $this->extension->configureOptions($resolver);
    }

    public function testConfigureOptionsForFrontend()
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefault')
            ->with('grid_widget_route', 'oro_frontend_datagrid_widget');

        $this->extension->configureOptions($resolver);
    }

    /**
     * @dataProvider viewDataProvider
     */
    public function testBuildView(bool $isFrontendRequest, string $route, string $expectedRoute)
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn($isFrontendRequest);

        $view = new FormView();
        $form = $this->createMock(FormInterface::class);
        $options = [];

        $view->vars['configs']['route_name'] = $route;
        $this->extension->buildView($view, $form, $options);

        $this->assertEquals($expectedRoute, $view->vars['configs']['route_name']);
    }

    public function viewDataProvider(): array
    {
        return [
            [false, 'oro_form_autocomplete_search', 'oro_form_autocomplete_search'],
            [true, 'custom_route', 'custom_route'],
            [true, 'oro_form_autocomplete_search', 'oro_frontend_autocomplete_search']
        ];
    }
}
