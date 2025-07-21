<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\CustomerBundle\Form\Extension\OroEntitySelectOrCreateInlineExtension;
use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OroEntitySelectOrCreateInlineExtensionTest extends TestCase
{
    private FrontendHelper&MockObject $frontendHelper;
    private OroEntitySelectOrCreateInlineExtension $extension;

    #[\Override]
    protected function setUp(): void
    {
        $this->frontendHelper = $this->createMock(FrontendHelper::class);

        $this->extension = new OroEntitySelectOrCreateInlineExtension($this->frontendHelper);
    }

    public function testGetExtendedTypes(): void
    {
        $this->assertEquals(
            [OroEntitySelectOrCreateInlineType::class],
            OroEntitySelectOrCreateInlineExtension::getExtendedTypes()
        );
    }

    public function testConfigureOptionsForBackend(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->never())
            ->method($this->anything());

        $this->extension->configureOptions($resolver);
    }

    public function testConfigureOptionsForFrontend(): void
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
    public function testBuildView(bool $isFrontendRequest, string $route, string $expectedRoute): void
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
