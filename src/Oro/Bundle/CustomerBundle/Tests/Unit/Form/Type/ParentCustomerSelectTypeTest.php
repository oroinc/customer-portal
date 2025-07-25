<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Form\Type\ParentCustomerSelectType;
use Oro\Bundle\FormBundle\Form\Type\OroJquerySelect2HiddenType;
use Oro\Component\Testing\ReflectionUtil;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParentCustomerSelectTypeTest extends TestCase
{
    private ParentCustomerSelectType $type;

    #[\Override]
    protected function setUp(): void
    {
        $this->type = new ParentCustomerSelectType();
    }

    public function testGetParent(): void
    {
        $this->assertEquals(OroJquerySelect2HiddenType::class, $this->type->getParent());
    }

    public function testConfigureOptions(): void
    {
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'))
            ->willReturnCallback(function (array $options) use ($resolver) {
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

                return $resolver;
            });

        $this->type->configureOptions($resolver);
    }

    /**
     * @dataProvider buildViewDataProvider
     */
    public function testBuildView(?object $parentData, ?int $expectedParentId): void
    {
        $parentForm = $this->createMock(FormInterface::class);
        $parentForm->expects($this->any())
            ->method('getData')
            ->willReturn($parentData);

        $formView = new FormView();

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->any())
            ->method('getParent')
            ->willReturn($parentForm);

        $this->type->buildView($formView, $form, []);

        $this->assertArrayHasKey('configs', $formView->vars);
        $this->assertArrayHasKey('entityId', $formView->vars['configs']);
        $this->assertEquals($expectedParentId, $formView->vars['configs']['entityId']);
    }

    public function buildViewDataProvider(): array
    {
        $customerId = 42;
        $customer = new Customer();
        ReflectionUtil::setId($customer, $customerId);

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
