<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CustomerBundle\Form\Type\CustomerSelectType;
use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerSelectTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CustomerSelectType
     */
    protected $type;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->type = new CustomerSelectType();
    }

    public function testGetParent()
    {
        $this->assertEquals(OroEntitySelectOrCreateInlineType::class, $this->type->getParent());
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
                    $this->assertEquals('oro_customer_customer', $options['autocomplete_alias']);
                    $this->assertEquals('oro_customer_customer_create', $options['create_form_route']);
                    $this->assertEquals(['placeholder' => 'oro.customer.customer.form.choose'], $options['configs']);
                }
            );

        $this->type->configureOptions($resolver);
    }
}
