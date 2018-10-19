<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Form\Extension\OroEntitySelectOrCreateInlineExtension;
use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OroEntitySelectOrCreateInlineExtensionTest extends AbstractCustomerUserAwareExtensionTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->extension = new OroEntitySelectOrCreateInlineExtension($this->tokenStorage);
    }

    public function testGetExtendedType()
    {
        $this->assertEquals(OroEntitySelectOrCreateInlineType::class, $this->extension->getExtendedType());
    }

    public function testConfigureOptionsNonCustomerUser()
    {
        $this->assertOptionsNotChangedForNonCustomerUser();
    }

    public function testConfigureOptionsCustomerUser()
    {
        $this->assertCustomerUserTokenCall();

        /** @var \PHPUnit\Framework\MockObject\MockObject|OptionsResolver $resolver */
        $resolver = $this->getMockBuilder('Symfony\Component\OptionsResolver\OptionsResolver')
            ->disableOriginalConstructor()
            ->getMock();
        $resolver->expects($this->once())
            ->method('setDefault')
            ->with('grid_widget_route', 'oro_frontend_datagrid_widget');

        $this->extension->configureOptions($resolver);
    }

    /**
     * @dataProvider viewDataProvider
     * @param object $user
     * @param string $route
     * @param string $expectedRoute
     */
    public function testBuildView($user, $route, $expectedRoute)
    {
        $this->assertCustomerUserTokenCall($user);

        $view = new FormView();
        /** @var FormInterface|\PHPUnit\Framework\MockObject\MockObject $form */
        $form = $this->createMock('Symfony\Component\Form\FormInterface');
        $options = [];

        $view->vars['configs']['route_name'] = $route;
        $this->extension->buildView($view, $form, $options);

        $this->assertEquals($expectedRoute, $view->vars['configs']['route_name']);
    }

    /**
     * @return array
     */
    public function viewDataProvider()
    {
        return [
            [new \stdClass(), 'oro_form_autocomplete_search', 'oro_form_autocomplete_search'],
            [new CustomerUser(), 'custom_route', 'custom_route'],
            [new CustomerUser(), 'oro_form_autocomplete_search', 'oro_frontend_autocomplete_search'],
        ];
    }
}
