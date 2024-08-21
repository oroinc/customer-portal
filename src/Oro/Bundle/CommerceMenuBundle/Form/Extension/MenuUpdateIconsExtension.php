<?php

namespace Oro\Bundle\CommerceMenuBundle\Form\Extension;

use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\FrontendBundle\Form\Type\StorefrontIconType;
use Oro\Bundle\NavigationBundle\Entity\MenuUpdateInterface;
use Oro\Bundle\NavigationBundle\Form\Type\MenuUpdateType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Adds menu item general fields to the {@see MenuUpdateType}.
 */
class MenuUpdateIconsExtension extends AbstractTypeExtension
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

                $form->add(
                    'icon',
                    StorefrontIconType::class,
                    [
                        'required' => false,
                    ]
                );
            }
        );
    }
}
