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
use Oro\Component\Testing\Unit\Form\Type\Stub\EntityType;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Validator\Validation;

abstract class AbstractCustomerUserRoleTypeTest extends FormIntegrationTestCase
{
    /**
     * @var Customer
     */
    protected static $customers;

    /**
     * @var CustomerUserRoleType
     */
    protected $formType;

    /**
     * @var array
     */
    protected $privilegeConfig = [
        'entity' => ['entity' => 'config'],
        'action' => ['action' => 'config'],
    ];

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->createCustomerUserRoleFormTypeAndSetDataClass();
        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        unset($this->formType);
    }

    /**
     * @return array
     */
    protected function getExtensions()
    {
        $entityIdentifierType = new EntityType([]);
        $customerSelectType = new EntityType($this->getCustomers(), CustomerSelectType::NAME);

        return [
            new PreloadedExtension(
                [
                    $this->formType,
                    EntityIdentifierType::class => $entityIdentifierType,
                    CustomerSelectType::class => $customerSelectType,
                    PrivilegeCollectionType::class => new PrivilegeCollectionType(),
                    AclPrivilegeType::class => new AclPriviledgeTypeStub(),
                ],
                []
            ),
            new ValidatorExtension(Validation::createValidator()),
        ];
    }

    public function submitDataProvider(): array
    {
        $roleLabel = 'customer_role_label';
        $alteredRoleLabel = 'altered_role_label';

        $defaultRole = new CustomerUserRole();
        $defaultRole->setLabel($roleLabel);

        $existingRoleBefore = new CustomerUserRole();
        ReflectionUtil::setId($existingRoleBefore, 1);
        $existingRoleBefore
            ->setLabel($roleLabel)
            ->setRole($roleLabel, false);

        $existingRoleAfter = new CustomerUserRole();
        ReflectionUtil::setId($existingRoleAfter, 1);
        $existingRoleAfter
            ->setLabel($alteredRoleLabel)
            ->setRole($roleLabel, false);

        return [
            'empty' => [
                'options' => ['privilege_config' => $this->privilegeConfig],
                'defaultData' => null,
                'viewData' => null,
                'submittedData' => [
                    'label' => $roleLabel,
                ],
                'expectedData' => $defaultRole,
            ],
            'existing' => [
                'options' => ['privilege_config' => $this->privilegeConfig],
                'defaultData' => $existingRoleBefore,
                'viewData' => $existingRoleBefore,
                'submittedData' => [
                    'label' => $alteredRoleLabel
                ],
                'expectedData' => $existingRoleAfter,
            ]
        ];
    }

    public function testFinishView()
    {
        $privilegeConfig = ['config'];
        $formView = new FormView();

        $this->formType->finishView(
            $formView,
            $this->createMock('Symfony\Component\Form\FormInterface'),
            ['privilege_config' => $privilegeConfig]
        );

        $this->assertArrayHasKey('privilegeConfig', $formView->vars);
        $this->assertEquals($privilegeConfig, $formView->vars['privilegeConfig']);
    }

    /**
     * @return Customer[]
     */
    protected function getCustomers()
    {
        if (!self::$customers) {
            self::$customers = [
                '1' => $this->createCustomer(1, 'first'),
                '2' => $this->createCustomer(2, 'second')
            ];
        }

        return self::$customers;
    }

    protected static function createCustomer(int $id, string $name): Customer
    {
        $customer = new Customer();
        ReflectionUtil::setId($customer, $id);
        $customer->setName($name);

        return $customer;
    }

    /**
     * Create form type
     */
    abstract protected function createCustomerUserRoleFormTypeAndSetDataClass();

    /**
     * @param array $options
     * @param CustomerUserRole|null $defaultData
     * @param CustomerUserRole|null $viewData
     * @param array $submittedData
     * @param CustomerUserRole|null $expectedData
     */
    abstract public function testSubmit(
        array $options,
        $defaultData,
        $viewData,
        array $submittedData,
        $expectedData
    );
}
