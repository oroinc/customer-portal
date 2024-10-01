<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerSelectType;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserRoleType;
use Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub\AclPriviledgeTypeStub;
use Oro\Bundle\FormBundle\Form\Type\EntityIdentifierType;
use Oro\Bundle\SecurityBundle\Form\Type\AclPrivilegeType;
use Oro\Bundle\SecurityBundle\Form\Type\PrivilegeCollectionType;
use Oro\Component\Testing\ReflectionUtil;
use Oro\Component\Testing\Unit\Form\Type\Stub\EntityTypeStub;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Validator\Validation;

class CustomerUserRoleTypeTest extends FormIntegrationTestCase
{
    private array $privilegeConfig = [
        'entity' => ['entity' => 'config'],
        'action' => ['action' => 'config'],
    ];

    private CustomerUserRoleType $formType;

    #[\Override]
    protected function setUp(): void
    {
        $this->formType = new CustomerUserRoleType();
        $this->formType->setDataClass(CustomerUserRole::class);

        parent::setUp();
    }

    #[\Override]
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension(
                [
                    $this->formType,
                    EntityIdentifierType::class => new EntityTypeStub(),
                    CustomerSelectType::class => new EntityTypeStub([
                        '1' => $this->getCustomer(1, 'first'),
                        '2' => $this->getCustomer(2, 'second')
                    ]),
                    new PrivilegeCollectionType(),
                    AclPrivilegeType::class => new AclPriviledgeTypeStub(),
                ],
                []
            ),
            new ValidatorExtension(Validation::createValidator()),
        ];
    }

    private function getCustomer(int $id, string $name): Customer
    {
        $customer = new Customer();
        ReflectionUtil::setId($customer, $id);
        $customer->setName($name);

        return $customer;
    }

    private function getCustomerUserRole(string $label, ?int $id = null): CustomerUserRole
    {
        $customerUserRole = new CustomerUserRole();
        $customerUserRole->setLabel($label);
        if (null !== $id) {
            ReflectionUtil::setId($customerUserRole, $id);
        }

        return $customerUserRole;
    }

    public function testBuildForm(): void
    {
        $form = $this->factory->create(
            CustomerUserRoleType::class,
            null,
            ['privilege_config' => $this->privilegeConfig]
        );
        $this->assertTrue($form->has('appendUsers'));
        $this->assertTrue($form->has('removeUsers'));
        $this->assertTrue($form->has('customer'));
        $this->assertTrue($form->has('selfManaged'));

        $formConfig = $form->getConfig();
        $this->assertEquals(CustomerUserRole::class, $formConfig->getOption('data_class'));
        $this->assertFalse($formConfig->getOption('hide_self_managed'));
    }

    public function testSubmitEmpty(): void
    {
        $roleLabel = 'customer_role_label';
        $defaultRole = $this->getCustomerUserRole($roleLabel);

        $form = $this->factory->create(
            CustomerUserRoleType::class,
            null,
            ['privilege_config' => $this->privilegeConfig]
        );
        $this->assertNull($form->getData());
        $this->assertNull($form->getViewData());

        $form->submit(['label' => $roleLabel]);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());

        $actualData = $form->getData();

        $actualDataWithDummyRole = clone $actualData;
        $actualDataWithDummyRole->setRole('', false);
        $expectedDataWithDummyRole = clone $defaultRole;
        $expectedDataWithDummyRole->setRole('', false);
        $this->assertEquals($actualDataWithDummyRole, $expectedDataWithDummyRole);

        $this->assertNotEmpty($actualData->getRole());
    }

    public function testSubmit(): void
    {
        $roleLabel = 'customer_role_label';
        $alteredRoleLabel = 'altered_role_label';

        $existingRoleBefore = $this->getCustomerUserRole($roleLabel, 1);
        $existingRoleBefore->setRole($roleLabel, false);

        $existingRoleAfter = $this->getCustomerUserRole($alteredRoleLabel, 1);
        $existingRoleAfter->setRole($roleLabel, false);

        $form = $this->factory->create(
            CustomerUserRoleType::class,
            $existingRoleBefore,
            ['privilege_config' => $this->privilegeConfig]
        );
        $this->assertSame($existingRoleBefore, $form->getData());
        $this->assertSame($existingRoleBefore, $form->getViewData());

        $form->submit(['label' => $alteredRoleLabel]);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());

        $actualData = $form->getData();

        $actualDataWithDummyRole = clone $actualData;
        $actualDataWithDummyRole->setRole('', false);
        $expectedDataWithDummyRole = clone $existingRoleAfter;
        $expectedDataWithDummyRole->setRole('', false);
        $this->assertEquals($actualDataWithDummyRole, $expectedDataWithDummyRole);

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
}
