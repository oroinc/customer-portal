<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerSelectType;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserRoleType;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendOwnerSelectType;
use Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub\AclPriviledgeTypeStub;
use Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub\FrontendOwnerSelectTypeStub;
use Oro\Bundle\FormBundle\Form\Type\EntityIdentifierType;
use Oro\Bundle\SecurityBundle\Form\Type\PrivilegeCollectionType;
use Oro\Component\Testing\ReflectionUtil;
use Oro\Component\Testing\Unit\Form\Type\Stub\EntityTypeStub;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Validation;

class FrontendCustomerUserRoleTypeTest extends AbstractCustomerUserRoleTypeTest
{
    /** @var CustomerUser[] */
    private $customerUsers = [];

    /** @var FrontendCustomerUserRoleType */
    protected $formType;

    /**
     * {@inheritDoc}
     */
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension(
                [
                    $this->formType,
                    EntityIdentifierType::class => new EntityTypeStub($this->getCustomerUsers()),
                    CustomerSelectType::class => new EntityTypeStub($this->getCustomers()),
                    new PrivilegeCollectionType(),
                    new AclPriviledgeTypeStub(),
                    FrontendOwnerSelectType::class => new FrontendOwnerSelectTypeStub(),
                ],
                []
            ),
            new ValidatorExtension(Validation::createValidator())
        ];
    }

    /**
     * @dataProvider submitDataProvider
     */
    public function testSubmit(
        array $options,
        ?CustomerUserRole $defaultData,
        ?CustomerUserRole $viewData,
        array $submittedData,
        ?CustomerUserRole $expectedData
    ) {
        $form = $this->factory->create(FrontendCustomerUserRoleType::class, $defaultData, $options);

        $this->assertTrue($form->has('appendUsers'));
        $this->assertTrue($form->has('removeUsers'));
        $this->assertTrue($form->has('customer'));
        $this->assertFalse($form->has('selfManaged'));

        $formConfig = $form->getConfig();
        $this->assertEquals(CustomerUserRole::class, $formConfig->getOption('data_class'));

        $this->assertTrue($formConfig->getOption('hide_self_managed'));

        $this->assertEquals($defaultData, $form->getData());
        $this->assertEquals($viewData, $form->getViewData());

        $form->submit($submittedData);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());

        $actualData = $form->getData();
        $this->assertEquals($expectedData, $actualData);

        if ($defaultData && $defaultData->getRole()) {
            $this->assertEquals($expectedData->getRole(), $actualData->getRole());
        } else {
            $this->assertNotEmpty($actualData->getRole());
        }
    }

    public function submitDataProvider(): array
    {
        $roleLabel = 'customer_role_label';
        $alteredRoleLabel = 'altered_role_label';
        $customer = new Customer();

        $defaultRole = new CustomerUserRole('');
        $defaultRole->setLabel($roleLabel);
        $defaultRole->setCustomer($customer);
        $existingRoleBefore = new CustomerUserRole();
        ReflectionUtil::setId($existingRoleBefore, 1);
        $existingRoleBefore
            ->setLabel($roleLabel)
            ->setRole($roleLabel, false)
            ->setCustomer($customer);

        $existingRoleAfter = new CustomerUserRole();
        ReflectionUtil::setId($existingRoleAfter, 1);
        $existingRoleAfter
            ->setLabel($alteredRoleLabel)
            ->setRole($roleLabel, false)
            ->setCustomer($customer);

        return [
            'empty' => [
                'options' => ['privilege_config' => $this->privilegeConfig],
                'defaultData' => $defaultRole,
                'viewData' => $defaultRole,
                'submittedData' => [
                    'label' => $roleLabel,
                    'customer' => $defaultRole->getCustomer()->getName()
                ],
                'expectedData' => $defaultRole
            ],
            'existing' => [
                'options' => ['privilege_config' => $this->privilegeConfig],
                'defaultData' => $existingRoleBefore,
                'viewData' => $existingRoleBefore,
                'submittedData' => [
                    'label' => $alteredRoleLabel,
                    'customer' => $existingRoleBefore->getCustomer()->getName()
                ],
                'expectedData' => $existingRoleAfter
            ]
        ];
    }

    /**
     * @dataProvider preSubmitProvider
     */
    public function testPreSubmit(array $data, array $expected)
    {
        $event = new FormEvent($this->prepareFormForEvents(), $data);

        $this->formType->preSubmit($event);

        $this->assertEquals($expected, $event->getData());
    }

    public function preSubmitProvider(): array
    {
        return [
            'append and remove users are empty' => [
                'data' => [
                    'customer' => '1',
                    'appendUsers' => '',
                    'removeUsers' => '',
                ],
                'expected' => [
                    'customer' => '1',
                    'appendUsers' => '1,4',
                    'removeUsers' => '',
                ]

            ],
            'append new user and remove one from predifined role' => [
                'data' => [
                    'customer' => '1',
                    'appendUsers' => '2',
                    'removeUsers' => '4',
                ],
                'expected' => [
                    'customer' => '1',
                    'appendUsers' => '1,2',
                    'removeUsers' => '4',
                ]

            ]
        ];
    }

    public function testPostSubmit()
    {
        [$customerUser1, , , $customerUser4] = array_values($this->getCustomerUsers());
        [$customer1] = array_values($this->getCustomers());

        $form = $this->prepareFormForEvents();
        $form->get('appendUsers')->setData([$customerUser1]);
        $form->get('removeUsers')->setData([$customerUser4]);

        $role = new CustomerUserRole();
        ReflectionUtil::setId($role, 1);
        $role->setCustomer($customer1);

        $event = new FormEvent($form, $role);

        $this->formType->postSubmit($event);

        $predefinedRole = $form->getConfig()->getOption('predefined_role');
        $this->assertTrue($predefinedRole->getCustomerUsers()->contains($customerUser4));
        $this->assertFalse($predefinedRole->getCustomerUsers()->contains($customerUser1));
    }

    /**
     * {@inheritdoc}
     */
    protected function createCustomerUserRoleFormTypeAndSetDataClass(): void
    {
        $this->formType = new FrontendCustomerUserRoleType();
        $this->formType->setDataClass(CustomerUserRole::class);
    }

    /**
     * @return CustomerUser[]
     */
    protected function getCustomerUsers(): array
    {
        if (!$this->customerUsers) {
            [$customer1, $customer2] = array_values($this->getCustomers());

            $customerUser1 = new CustomerUser();
            ReflectionUtil::setId($customerUser1, 1);
            $customerUser1->setCustomer($customer1);

            $customerUser2 = new CustomerUser();
            ReflectionUtil::setId($customerUser2, 2);
            $customerUser2->setCustomer($customer2);

            $customerUser3 = new CustomerUser();
            ReflectionUtil::setId($customerUser3, 3);

            $customerUser4 = new CustomerUser();
            ReflectionUtil::setId($customerUser4, 4);
            $customerUser4->setCustomer($customer1);

            $this->customerUsers = [
                $customerUser1->getId() => $customerUser1,
                $customerUser2->getId() => $customerUser2,
                $customerUser3->getId() => $customerUser3,
                $customerUser4->getId() => $customerUser4,
            ];
        }

        return $this->customerUsers;
    }

    private function prepareFormForEvents(): FormInterface
    {
        [$customerUser1, $customerUser2, $customerUser3, $customerUser4] = array_values($this->getCustomerUsers());

        $role = new CustomerUserRole();
        ReflectionUtil::setId($role, 1);

        $predefinedRole = new CustomerUserRole();
        ReflectionUtil::setId($predefinedRole, 2);
        $predefinedRole->addCustomerUser($customerUser1);
        $predefinedRole->addCustomerUser($customerUser2);
        $predefinedRole->addCustomerUser($customerUser3);
        $predefinedRole->addCustomerUser($customerUser4);

        return $this->factory->create(
            FrontendCustomerUserRoleType::class,
            $role,
            ['privilege_config' => $this->privilegeConfig, 'predefined_role' => $predefinedRole]
        );
    }
}
