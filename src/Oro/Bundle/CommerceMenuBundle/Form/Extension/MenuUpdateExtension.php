<?php

namespace Oro\Bundle\CommerceMenuBundle\Form\Extension;

use Oro\Bundle\AttachmentBundle\Form\Type\ImageType;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\CommerceMenuBundle\Form\Type\MenuScreensConditionType;
use Oro\Bundle\CommerceMenuBundle\Form\Type\MenuUserAgentConditionsCollectionType;
use Oro\Bundle\NavigationBundle\Form\Type\MenuUpdateType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class MenuUpdateExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $data = $event->getData();
                if ($data instanceof MenuUpdate) {
                    $form = $event->getForm();
                    $form
                        ->add(
                            'image',
                            ImageType::class,
                            [
                                'label' => 'oro.commercemenu.menuupdate.image.label',
                                'required' => false
                            ]
                        )
                        ->add(
                            'condition',
                            TextType::class,
                            [
                                'required' => false,
                                'label' => 'oro.commercemenu.menuupdate.condition.label',
                                'tooltip' => 'oro.commercemenu.form.tooltip.menu_item_condition'
                            ]
                        )
                        ->add(
                            'menuUserAgentConditions',
                            MenuUserAgentConditionsCollectionType::class,
                            [
                                'required' => false,
                                'label' =>
                                    'oro.commercemenu.menuupdate.menu_user_agent_conditions_collection.label',
                            ]
                        )
                        ->add(
                            'screens',
                            MenuScreensConditionType::class,
                            [
                                'required' => false,
                                'label' =>
                                    'oro.commercemenu.menuupdate.menu_screens_condition.label',
                            ]
                        );
                }
            }
        );
    }

    /**
     * {@inheritDoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [MenuUpdateType::class];
    }
}
