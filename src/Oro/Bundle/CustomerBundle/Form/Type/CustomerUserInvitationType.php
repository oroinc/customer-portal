<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserInvitation;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRoleRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Back-office form for inviting a customer user on behalf of an existing customer user.
 */
class CustomerUserInvitationType extends AbstractType
{
    public const NAME = 'oro_customer_customer_user_invitation';

    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly AclHelper $aclHelper
    ) {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'oro.customer.customeruserinvitation.email.label',
                'required' => true,
                'attr' => ['autocomplete' => 'email']
            ])
            ->add('invitedBy', CustomerUserSelectType::class, [
                'label' => 'oro.customer.customeruserinvitation.invited_by_customer_user.label',
                'required' => true
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData'])
            ->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    public function onPreSetData(FormEvent $event): void
    {
        /** @var CustomerUserInvitation $invitation */
        $invitation = $event->getData();
        $this->addCustomerAndRoles($event->getForm(), $invitation->getCustomer());
    }

    public function onPreSubmit(FormEvent $event): void
    {
        $data = $event->getData();
        $customer = is_array($data) ? ($data['customer'] ?? null) : null;
        $this->addCustomerAndRoles($event->getForm(), $customer);
    }

    private function addCustomerAndRoles(FormInterface $form, Customer|int|string|null $customer): void
    {
        $form
            ->add('customer', CustomerSelectType::class, [
                'label' => 'oro.customer.customeruserinvitation.customer.label',
                'required' => true
            ])
            ->add('roles', CustomerUserRoleSelectType::class, [
                'label' => 'oro.customer.customeruserinvitation.roles.label',
                'choices' => $this->getAvailableRoles($customer),
                'required' => true,
                'by_reference' => false
            ]);
    }

    private function getAvailableRoles(Customer|int|string|null $customer): array
    {
        /** @var CustomerUserRoleRepository $repository */
        $repository = $this->doctrine->getRepository(CustomerUserRole::class);
        $customerId = null;
        if ($customer instanceof Customer) {
            $customerId = $customer->getId();
        } elseif ($customer !== null) {
            $customerId = (int)$customer;
        }
        $availableRolesQB = $repository->getAvailableRolesByCustomerUserQueryBuilder($customerId);

        return $this->aclHelper->apply($availableRolesQB)->getResult();
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CustomerUserInvitation::class,
            'csrf_token_id' => 'customer_user_invitation'
        ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
