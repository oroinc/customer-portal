<?php

namespace Oro\Bundle\CommerceMenuBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Oro\Bundle\AttachmentBundle\Form\Type\ImageType;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\NavigationBundle\Form\Type\MenuUpdateType;

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
                        'oro_image',
                        [
                            'label' => 'oro.commercemenu.menuupdate.image.label',
                            'required' => false
                        ]
                    )
                        ->add(
                        'condition',
                        'text',
                        [
                            'required' => false,
                            'label' => 'oro.commercemenu.menuupdate.condition.label',
                            'tooltip' => 'oro.commercemenu.form.tooltip.menu_item_condition'
                        ]
                    );
                }
            }
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getExtendedType()
    {
        return MenuUpdateType::class;
    }
}
