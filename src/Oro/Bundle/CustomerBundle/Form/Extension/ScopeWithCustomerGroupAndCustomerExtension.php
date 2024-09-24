<?php

namespace Oro\Bundle\CustomerBundle\Form\Extension;

use Oro\Bundle\CustomerBundle\Validator\Constraints\ScopeWithCustomerGroupAndCustomer;
use Oro\Bundle\FormBundle\Utils\FormUtils;
use Oro\Bundle\ScopeBundle\Form\Type\ScopeCollectionType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;

/**
 * Adds additional validation for scopes collection.
 * Can not use scopes at the same time as custom and customer group.
 */
class ScopeWithCustomerGroupAndCustomerExtension extends AbstractTypeExtension
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (PreSubmitEvent $event) {
            $form = $event->getForm();
            $constraints = ['constraints' => [new ScopeWithCustomerGroupAndCustomer()]];
            foreach ($form as $index => $field) {
                FormUtils::mergeFieldOptionsRecursive($form, $index, $constraints);
            }
        });
    }

    #[\Override]
    public static function getExtendedTypes(): iterable
    {
        return [ScopeCollectionType::class];
    }
}
