<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

class CustomerUserRoleType extends AbstractCustomerUserRoleType
{
    public const NAME = 'oro_customer_customer_user_role';

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
