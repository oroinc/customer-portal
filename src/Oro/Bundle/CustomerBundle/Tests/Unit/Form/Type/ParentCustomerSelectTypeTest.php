<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Form\Type\ParentCustomerSelectType;
use Oro\Bundle\FormBundle\Form\Type\OroJquerySelect2HiddenType;
use Symfony\Component\Form\FormView;

class ParentCustomerSelectTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ParentCustomerSelectType
     */
    protected $type;

    protected function setUp()
    {
        $this->type = new ParentCustomerSelectType();
    }

    public function testGetParent()
    {
        $this->assertEquals(OroJquerySelect2HiddenType::class, $this->type->getParent());
    }

    public function testConfigureOptions()
    {
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolver');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'))
            ->willReturnCallback(
                function (array $options) {
                    $this->assertArrayHasKey('autocomplete_alias', $options);
                    $this->assertArrayHasKey('configs', $options);
                    $this->assertEquals('oro_customer_parent', $options['autocomplete_alias']);
                    $this->assertEquals(
                        [
                            'component' => 'autocomplete-entity-parent',
                            'placeholder' => 'oro.customer.customer.form.choose_parent'
                        ],
                        $options['configs']
                    );
                }
            );

        $this->type->configureOptions($resolver);
    }

    /**
     * @param object|null $parentData
     * @param int|null $expectedParentId
     * @dataProvider buildViewDataProvider
     */
    public function testBuildView($parentData, $expectedParentId)
    {
        $parentForm = $this->createMock('Symfony\Component\Form\FormInterface');
        $parentForm->expects($this->any())
            ->method('getData')
            ->willReturn($parentData);

        $formView = new FormView();

        $form = $this->createMock('Symfony\Component\Form\FormInterface');
        $form->expects($this->any())
            ->method('getParent')
            ->willReturn($parentForm);

        $this->type->buildView($formView, $form, []);

        $this->assertArrayHasKey('configs', $formView->vars);
        $this->assertArrayHasKey('entityId', $formView->vars['configs']);
        $this->assertEquals($expectedParentId, $formView->vars['configs']['entityId']);
    }

    /**
     * @return array
     */
    public function buildViewDataProvider()
    {
        $customerId = 42;
        $customer = new Customer();

        $reflection = new \ReflectionProperty(get_class($customer), 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($customer, $customerId);

        return [
            'without customer' => [
                'parentData' => null,
                'expectedParentId' => null,
            ],
            'with customer' => [
                'parentData' => $customer,
                'expectedParentId' => $customerId,
            ],
        ];
    }
}
