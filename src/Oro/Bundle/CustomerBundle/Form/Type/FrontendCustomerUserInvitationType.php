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
 * Storefront form used by customer account administrators to invite a user.
 */
class FrontendCustomerUserInvitationType extends AbstractType
{
    public const NAME = 'oro_customer_frontend_customer_user_invitation';

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
            ->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData'])
            ->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    public function onPreSetData(FormEvent $event): void
    {
        /** @var CustomerUserInvitation $invitation */
        $invitation = $event->getData();
        $this->addCustomerAndRoles($event->getForm(), $invitation, $invitation->getCustomer());
    }

    public function onPreSubmit(FormEvent $event): void
    {
        /** @var CustomerUserInvitation $invitation */
        $invitation = $event->getForm()->getData();
        $data = $event->getData();
        $customer = is_array($data) ? ($data['customer'] ?? null) : null;

        $this->addCustomerAndRoles($event->getForm(), $invitation, $customer);
    }

    private function addCustomerAndRoles(
        FormInterface $form,
        CustomerUserInvitation $invitation,
        Customer|int|string|null $customer
    ): void {
        $form
            ->add('customer', FrontendOwnerSelectType::class, [
                'label' => 'oro.customer.customeruserinvitation.customer.label',
                'targetObject' => $invitation,
                'required' => true,
                'row_attr' => $this->hasSubCustomers($invitation->getCustomer()) ? [] : ['class' => 'hidden']
            ])
            ->add('roles', FrontendCustomerUserRoleSelectType::class, [
                'label' => 'oro.customer.customeruserinvitation.roles.label',
                'choices' => $this->getAvailableRoles($customer),
                'required' => true,
                'by_reference' => false
            ]);
    }

    private function hasSubCustomers(?Customer $customer): bool
    {
        return $customer !== null && !$customer->getChildren()->isEmpty();
    }

    private function getAvailableRoles(Customer|int|string|null $customer): array
    {
        $customerId = null;
        if ($customer instanceof Customer) {
            $customerId = $customer->getId();
        } elseif ($customer !== null) {
            $customerId = (int)$customer;
        }

        /** @var CustomerUserRoleRepository $repository */
        $repository = $this->doctrine->getRepository(CustomerUserRole::class);
        $availableRolesQB = $repository->getAvailableRolesByCustomerUserQueryBuilder($customerId);

        return $this->aclHelper->apply($availableRolesQB)->getResult();
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CustomerUserInvitation::class,
            'csrf_token_id' => 'customer_user_invitation',
            'dynamic_fields_disabled' => true
        ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
