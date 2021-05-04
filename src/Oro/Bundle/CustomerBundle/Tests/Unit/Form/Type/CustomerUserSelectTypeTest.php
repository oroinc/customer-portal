<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserSelectType;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserType;
use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerUserSelectTypeTest extends FormIntegrationTestCase
{
    /**
     * @var CustomerUserSelectType
     */
    protected $formType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->formType = new CustomerUserSelectType();
    }

    public function testGetParent()
    {
        $this->assertEquals(OroEntitySelectOrCreateInlineType::class, $this->formType->getParent());
    }

    public function testConfigureOptions()
    {
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'))
            ->willReturnCallback(
                function (array $options) {
                    $this->assertArrayHasKey('autocomplete_alias', $options);
                    $this->assertArrayHasKey('create_form_route', $options);
                    $this->assertArrayHasKey('configs', $options);
                    $this->assertEquals(CustomerUserType::class, $options['autocomplete_alias']);
                    $this->assertEquals('oro_customer_customer_user_create', $options['create_form_route']);
                    $this->assertEquals(
                        [
                            'component' => 'autocomplete-customeruser',
                            'placeholder' => 'oro.customer.customeruser.form.choose',
                        ],
                        $options['configs']
                    );
                }
            );

        $this->formType->configureOptions($resolver);
    }
}
