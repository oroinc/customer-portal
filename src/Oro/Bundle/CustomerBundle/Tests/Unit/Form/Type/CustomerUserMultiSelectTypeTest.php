<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserMultiSelectType;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserType;
use Oro\Bundle\UserBundle\Form\Type\UserMultiSelectType;
use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Component\Testing\Unit\Form\Type\Stub\EntityType;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerUserMultiSelectTypeTest extends FormIntegrationTestCase
{
    use EntityTrait;

    public function testConfigureOptions()
    {
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                [
                    'autocomplete_alias' => CustomerUserType::class,
                    'configs' => [
                        'multiple' => true,
                        'component' => 'autocomplete-customeruser',
                        'placeholder' => 'oro.customer.customeruser.form.choose',
                    ],
                    'attr' => [
                        'class' => 'customer-customeruser-multiselect',
                    ],
                ]
            );

        $formType = new CustomerUserMultiSelectType();
        $formType->configureOptions($resolver);
    }

    public function testGetParent()
    {
        $formType = new CustomerUserMultiSelectType();
        $this->assertEquals(UserMultiSelectType::class, $formType->getParent());
    }

    /**
     * @dataProvider submitProvider
     *
     * @param array $defaultData
     * @param array $submittedData
     * @param bool $isValid
     * @param array $expectedData
     */
    public function testSubmit(
        array $defaultData,
        array $submittedData,
        bool $isValid = false,
        array $expectedData = []
    ) {
        $form = $this->factory->create(CustomerUserMultiSelectType::class, $defaultData, []);

        $this->assertEquals($defaultData, $form->getData());

        $form->submit($submittedData);

        $this->assertEquals($isValid, $form->isValid(), $form->getErrors(true));
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedData, $form->getData());
    }

    /**
     * @return array
     */
    public function submitProvider()
    {
        return [
            'empty data' => [
                'defaultData' => [],
                'submittedData' => [],
                'isValid' => true,
                'expectedData' => []
            ],
            'valid data' => [
                'defaultData' => [$this->getCustomerUser(1)],
                'submittedData' => [2, 3],
                'isValid' => true,
                'expectedData' => [$this->getCustomerUser(2), $this->getCustomerUser(3)]
            ],
            'invalid data' => [
                'defaultData' => [$this->getCustomerUser(1)],
                'submittedData' => [5]
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtensions()
    {
        $customerUserSelectType = new EntityType(
            [
                1 => $this->getCustomerUser(1),
                2 => $this->getCustomerUser(2),
                3 => $this->getCustomerUser(3),
            ],
            UserMultiSelectType::NAME,
            [
                'multiple' => true,
            ]
        );
        return [
            new PreloadedExtension(
                [
                    UserMultiSelectType::class => $customerUserSelectType,
                ],
                []
            ),
            $this->getValidatorExtension(false),
        ];
    }

    /**
     * @param int $id
     * @return CustomerUser
     */
    protected function getCustomerUser($id)
    {
        return $this->getEntity('Oro\Bundle\CustomerBundle\Entity\CustomerUser', ['id' => $id, 'salt' => $id]);
    }
}
