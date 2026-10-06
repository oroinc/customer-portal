<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserInvitation;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRoleRepository;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerSelectType;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserInvitationType;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserRoleSelectType;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserSelectType;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Component\Testing\ReflectionUtil;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerUserInvitationTypeTest extends TestCase
{
    private ManagerRegistry&MockObject $doctrine;
    private AclHelper&MockObject $aclHelper;
    private CustomerUserInvitationType $formType;

    #[\Override]
    protected function setUp(): void
    {
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->aclHelper = $this->createMock(AclHelper::class);
        $this->formType = new CustomerUserInvitationType($this->doctrine, $this->aclHelper);
    }

    public function testBuildForm(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $fields = new \ArrayObject();
        $listeners = new \ArrayObject();
        $builder->expects(self::exactly(2))
            ->method('add')
            ->willReturnCallback(
                static function (string $name, string $type, array $options) use ($fields, $builder) {
                    $fields[$name] = [$type, $options];

                    return $builder;
                }
            );
        $builder->expects(self::exactly(2))
            ->method('addEventListener')
            ->willReturnCallback(
                static function (string $eventName, callable $listener) use ($listeners, $builder) {
                    $listeners[$eventName] = $listener;

                    return $builder;
                }
            );

        $this->formType->buildForm($builder, []);

        self::assertEquals(EmailType::class, $fields['email'][0]);
        self::assertEquals(CustomerUserSelectType::class, $fields['invitedBy'][0]);
        self::assertEquals([$this->formType, 'onPreSetData'], $listeners[FormEvents::PRE_SET_DATA]);
        self::assertEquals([$this->formType, 'onPreSubmit'], $listeners[FormEvents::PRE_SUBMIT]);
    }

    public function testOnPreSetDataLoadsRolesForCustomer(): void
    {
        $customer = new Customer();
        ReflectionUtil::setId($customer, 42);
        $invitation = (new CustomerUserInvitation())->setCustomer($customer);
        $roles = [new CustomerUserRole('ROLE_BUYER')];
        $this->expectAvailableRoles(42, $roles);

        $form = $this->createMock(FormInterface::class);
        $addedFields = $this->expectDynamicFields($form);

        $this->formType->onPreSetData(new FormEvent($form, $invitation));

        self::assertEquals(CustomerSelectType::class, $addedFields['customer'][0]);
        self::assertEquals(CustomerUserRoleSelectType::class, $addedFields['roles'][0]);
        self::assertEquals($roles, $addedFields['roles'][1]['choices']);
    }

    public function testOnPreSubmitLoadsRolesWithoutCustomer(): void
    {
        $this->expectAvailableRoles(null, []);
        $form = $this->createMock(FormInterface::class);
        $addedFields = $this->expectDynamicFields($form);

        $this->formType->onPreSubmit(new FormEvent($form, []));

        self::assertEquals([], $addedFields['roles'][1]['choices']);
    }

    public function testConfigureOptions(): void
    {
        $resolver = new OptionsResolver();
        $this->formType->configureOptions($resolver);

        $options = $resolver->resolve();
        self::assertEquals(CustomerUserInvitation::class, $options['data_class']);
        self::assertEquals('customer_user_invitation', $options['csrf_token_id']);
        self::assertEquals(CustomerUserInvitationType::NAME, $this->formType->getBlockPrefix());
    }

    private function expectAvailableRoles(?int $customerId, array $roles): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);
        $repository = $this->createMock(CustomerUserRoleRepository::class);

        $this->doctrine->expects(self::once())
            ->method('getRepository')
            ->with(CustomerUserRole::class)
            ->willReturn($repository);
        $repository->expects(self::once())
            ->method('getAvailableRolesByCustomerUserQueryBuilder')
            ->with($customerId)
            ->willReturn($queryBuilder);
        $this->aclHelper->expects(self::once())
            ->method('apply')
            ->with($queryBuilder)
            ->willReturn($query);
        $query->expects(self::once())
            ->method('getResult')
            ->willReturn($roles);
    }

    private function expectDynamicFields(FormInterface&MockObject $form): \ArrayObject
    {
        $addedFields = new \ArrayObject();
        $form->expects(self::exactly(2))
            ->method('add')
            ->willReturnCallback(
                static function (string $name, string $type, array $options) use ($addedFields, $form) {
                    $addedFields[$name] = [$type, $options];

                    return $form;
                }
            );

        return $addedFields;
    }
}
