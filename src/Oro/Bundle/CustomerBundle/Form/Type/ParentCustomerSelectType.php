<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\FormBundle\Form\Type\OroJquerySelect2HiddenType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParentCustomerSelectType extends AbstractType
{
    const NAME = 'oro_customer_parent_select';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'oro_customer_parent',
                'configs' => [
                    'component' => 'autocomplete-entity-parent',
                    'placeholder' => 'oro.customer.customer.form.choose_parent'
                ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $parentData = $form->getParent()->getData();
        $customerId = null;
        if ($parentData instanceof Customer) {
            $customerId = $parentData->getId();
        }
        $view->vars['configs']['entityId'] = $customerId;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return OroJquerySelect2HiddenType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
}
