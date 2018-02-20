<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CustomerBundle\Form\Type\CustomerGroupSelectType;
use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;

class CustomerGroupSelectTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CustomerGroupSelectType
     */
    protected $type;

    protected function setUp()
    {
        $this->type = new CustomerGroupSelectType();
    }

    public function testGetName()
    {
        $this->assertEquals(CustomerGroupSelectType::NAME, $this->type->getName());
    }

    public function testGetParent()
    {
        $this->assertEquals(OroEntitySelectOrCreateInlineType::NAME, $this->type->getParent());
    }

    public function testConfigureOptions()
    {
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolver');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                $this->callback(
                    function (array $options) {
                        $this->assertArrayHasKey('autocomplete_alias', $options);
                        $this->assertArrayHasKey('create_form_route', $options);
                        $this->assertArrayHasKey('configs', $options);
                        $this->assertEquals('oro_customer_group', $options['autocomplete_alias']);
                        $this->assertEquals('oro_customer_customer_group_create', $options['create_form_route']);
                        $this->assertEquals(
                            ['placeholder' => 'oro.customer.customergroup.form.choose'],
                            $options['configs']
                        );

                        return true;
                    }
                )
            );

        $this->type->configureOptions($resolver);
    }
}
