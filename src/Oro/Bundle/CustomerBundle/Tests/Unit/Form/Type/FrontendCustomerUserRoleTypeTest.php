<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
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
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Validator\Validation;

class FrontendCustomerUserRoleTypeTest extends FormIntegrationTestCase
{
    private array $privilegeConfig = [
        'entity' => ['entity' => 'config'],
        'action' => ['action' => 'config'],
    ];

    private Customer $customer1;
    private Customer $customer2;
    private CustomerUser $customerUser1;
    private CustomerUser $customerUser2;
    private CustomerUser $customerUser3;
    private CustomerUser $customerUser4;
    private FrontendCustomerUserRoleType $formType;

    protected function setUp(): void
    {
        $this->formType = new FrontendCustomerUserRoleType();
        $this->formType->setDataClass(CustomerUserRole::class);

        $this->customer1 = $this->getCustomer(1, 'first');
        $this->customer2 = $this->getCustomer(2, 'second');
        $this->customerUser1 = $this->getCustomerUser(1, $this->customer1);
        $this->customerUser2 = $this->getCustomerUser(2, $this->customer2);
        $this->customerUser3 = $this->getCustomerUser(3, null);
        $this->customerUser4 = $this->getCustomerUser(4, $this->customer1);

        parent::setUp();
    }

    /**
     * {@inheritDoc}
     */
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension(
                [
                    $this->formType,
                    EntityIdentifierType::class => new EntityTypeStub([
                        $this->customerUser1->getId() => $this->customerUser1,
                        $this->customerUser2->getId() => $this->customerUser2,
                        $this->customerUser3->getId() => $this->customerUser3,
                        $this->customerUser4->getId() => $this->customerUser4
                    ]),
                    FrontendOwnerSelectType::class => new FrontendOwnerSelectTypeStub([
                        $this->customer1->getId() => $this->customer1,
                        $this->customer2->getId() => $this->customer2
                    ]),
                    new PrivilegeCollectionType(),
                    new AclPriviledgeTypeStub(),
                ],
                []
            ),
            new ValidatorExtension(Validation::createValidator())
        ];
    }

    private function getCustomer(int $id, string $name): Customer
    {
        $customer = new Customer();
        ReflectionUtil::setId($customer, $id);
        $customer->setName($name);

        return $customer;
    }

    private function getCustomerUser(int $id, ?Customer $customer): CustomerUser
    {
        $customerUser = new CustomerUser();
        ReflectionUtil::setId($customerUser, $id);
        if (null !== $customer) {
            $customerUser->setCustomer($customer);
        }

        return $customerUser;
    }

    private function getCustomerUserRole(int $id): CustomerUserRole
    {
        $customerUserRole = new CustomerUserRole();
        ReflectionUtil::setId($customerUserRole, $id);

        return $customerUserRole;
    }

    private function prepareFormForEvents(): FormInterface
    {
        $role = $this->getCustomerUserRole(1);

        $predefinedRole = $this->getCustomerUserRole(2);
        $predefinedRole->addCustomerUser($this->customerUser1);
        $predefinedRole->addCustomerUser($this->customerUser2);
        $predefinedRole->addCustomerUser($this->customerUser3);
        $predefinedRole->addCustomerUser($this->customerUser4);

        return $this->factory->create(
            FrontendCustomerUserRoleType::class,
            $role,
            ['privilege_config' => $this->privilegeConfig, 'predefined_role' => $predefinedRole]
        );
    }

    public function testBuildForm(): void
    {
        $form = $this->factory->create(
            FrontendCustomerUserRoleType::class,
            null,
            ['privilege_config' => $this->privilegeConfig]
        );
        $this->assertTrue($form->has('appendUsers'));
        $this->assertTrue($form->has('removeUsers'));
        $this->assertTrue($form->has('customer'));
        $this->assertFalse($form->has('selfManaged'));

        $formConfig = $form->getConfig();
        $this->assertEquals(CustomerUserRole::class, $formConfig->getOption('data_class'));
        $this->assertTrue($formConfig->getOption('hide_self_managed'));
    }

    public function testSubmitEmpty(): void
    {
        $roleLabel = 'customer_role_label';

        $defaultRole = new CustomerUserRole('');
        $defaultRole->setLabel($roleLabel);
        $defaultRole->setCustomer($this->customer1);

        $form = $this->factory->create(
            FrontendCustomerUserRoleType::class,
            $defaultRole,
            ['privilege_config' => $this->privilegeConfig]
        );
        $this->assertEquals($defaultRole, $form->getData());
        $this->assertEquals($defaultRole, $form->getViewData());

        $form->submit([
            'label' => $roleLabel,
            'customer' => $defaultRole->getCustomer()->getId()
        ]);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());

        $actualData = $form->getData();
        $this->assertEquals($defaultRole, $actualData);

        $this->assertNotEmpty($actualData->getRole());
    }

    public function testSubmit(): void
    {
        $roleLabel = 'customer_role_label';
        $alteredRoleLabel = 'altered_role_label';

        $existingRoleBefore = $this->getCustomerUserRole(1);
        $existingRoleBefore->setLabel($roleLabel);
        $existingRoleBefore->setRole($roleLabel, false);
        $existingRoleBefore->setCustomer($this->customer1);

        $existingRoleAfter = $this->getCustomerUserRole(1);
        $existingRoleAfter->setLabel($alteredRoleLabel);
        $existingRoleAfter->setRole($roleLabel, false);
        $existingRoleAfter->setCustomer($this->customer1);

        $form = $this->factory->create(
            FrontendCustomerUserRoleType::class,
            $existingRoleBefore,
            ['privilege_config' => $this->privilegeConfig]
        );
        $this->assertEquals($existingRoleBefore, $form->getData());
        $this->assertEquals($existingRoleBefore, $form->getViewData());

        $form->submit([
            'label' => $alteredRoleLabel,
            'customer' => $existingRoleBefore->getCustomer()->getId()
        ]);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());

        $actualData = $form->getData();
        $this->assertEquals($existingRoleAfter, $actualData);

        $this->assertEquals($existingRoleAfter->getRole(), $actualData->getRole());
    }

    public function testFinishView(): void
    {
        $privilegeConfig = ['config'];
        $formView = new FormView();

        $this->formType->finishView(
            $formView,
            $this->createMock(FormInterface::class),
            ['privilege_config' => $privilegeConfig]
        );

        $this->assertArrayHasKey('privilegeConfig', $formView->vars);
        $this->assertEquals($privilegeConfig, $formView->vars['privilegeConfig']);
    }

    /**
     * @dataProvider preSubmitProvider
     */
    public function testPreSubmit(array $data, array $expected): void
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

    public function testPostSubmit(): void
    {
        $form = $this->prepareFormForEvents();
        $form->get('appendUsers')->setData([$this->customerUser1]);
        $form->get('removeUsers')->setData([$this->customerUser4]);

        $role = $this->getCustomerUserRole(1);
        $role->setCustomer($this->customer1);

        $this->formType->postSubmit(new FormEvent($form, $role));

        $predefinedRole = $form->getConfig()->getOption('predefined_role');
        $this->assertTrue($predefinedRole->getCustomerUsers()->contains($this->customerUser4));
        $this->assertFalse($predefinedRole->getCustomerUsers()->contains($this->customerUser1));
    }
}
