<?php

namespace Oro\Bundle\CustomerBundle\Form\Extension;

use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserType;
use Oro\Bundle\CustomerBundle\Form\Type\EnabledLocalizationSelectType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * This form extension add preferred localization field for Customer User create/edit form.
 */
class PreferredLocalizationCustomerUserExtension extends AbstractTypeExtension
{
    public const PREFERRED_LOCALIZATION_FIELD = 'enabledLocalization';

    /**
     * @var EventSubscriberInterface
     */
    protected $eventSubscriber;

    public function __construct(EventSubscriberInterface $eventSubscriber)
    {
        $this->eventSubscriber = $eventSubscriber;
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [CustomerUserType::class];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            self::PREFERRED_LOCALIZATION_FIELD,
            EnabledLocalizationSelectType::class,
            [
                'label' => 'oro.customer.customeruser.preferred_localization.label',
                'required' => false,
                'mapped' => false,
                'configs' => [
                    'component' => 'autocomplete-enabledlocalization',
                    'placeholder' => 'oro.customer.customeruser.preferred_localization.placeholder',
                ],
            ]
        );

        $builder->addEventSubscriber($this->eventSubscriber);
    }
}
