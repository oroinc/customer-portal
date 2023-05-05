<?php

namespace Oro\Bundle\CommerceMenuBundle\Form\Extension;

use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\CommerceMenuBundle\Form\Type\MenuScreensConditionType;
use Oro\Bundle\CommerceMenuBundle\Form\Type\MenuUserAgentConditionsCollectionType;
use Oro\Bundle\NavigationBundle\Entity\MenuUpdateInterface;
use Oro\Bundle\NavigationBundle\Form\Type\MenuUpdateType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Adds menu item conditions fields to the {@see MenuUpdateType}.
 */
class MenuUpdateConditionsExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [MenuUpdateType::class];
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                /** @var MenuUpdateInterface $menuUpdate */
                $menuUpdate = $event->getData();
                if (!$menuUpdate instanceof MenuUpdate) {
                    return;
                }

                $form = $event->getForm();
                $form
                    ->add(
                        'condition',
                        TextType::class,
                        [
                            'required' => false,
                            'label' => 'oro.commercemenu.menuupdate.condition.label',
                            'tooltip' => 'oro.commercemenu.menuupdate.condition.tooltip',
                        ]
                    )
                    ->add(
                        'menuUserAgentConditions',
                        MenuUserAgentConditionsCollectionType::class,
                        [
                            'required' => false,
                            'label' => 'oro.commercemenu.menuupdate.menu_user_agent_conditions_collection.label',
                        ]
                    )
                    ->add(
                        'screens',
                        MenuScreensConditionType::class,
                        [
                            'required' => false,
                            'label' => 'oro.commercemenu.menuupdate.menu_screens_condition.label',
                        ]
                    );
            }
        );
    }
}
