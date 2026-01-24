<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * Builds a form for creating and editing customer user roles with customer assignment.
 *
 * This form type extends AbstractCustomerUserRoleType and adds a customer selection field,
 * allowing administrators to assign roles to specific customers. It inherits base role
 * configuration from the parent class and adds customer-specific functionality.
 */
class CustomerUserRoleType extends AbstractCustomerUserRoleType
{
    const NAME = 'oro_customer_customer_user_role';

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add(
            'customer',
            CustomerSelectType::class,
            [
                'required' => false,
                'label' => 'oro.customer.customeruserrole.customer.label'
            ]
        );
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
