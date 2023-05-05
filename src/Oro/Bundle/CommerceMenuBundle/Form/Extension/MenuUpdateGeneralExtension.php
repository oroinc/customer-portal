<?php

namespace Oro\Bundle\CommerceMenuBundle\Form\Extension;

use Oro\Bundle\AttachmentBundle\Form\Type\ImageType;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\CommerceMenuBundle\Provider\MenuTemplatesProvider;
use Oro\Bundle\FormBundle\Form\Type\LinkTargetType;
use Oro\Bundle\NavigationBundle\Entity\MenuUpdateInterface;
use Oro\Bundle\NavigationBundle\Form\Type\MenuUpdateType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Adds menu item general fields to the {@see MenuUpdateType}.
 */
class MenuUpdateGeneralExtension extends AbstractTypeExtension
{
    private MenuTemplatesProvider $menuTemplatesProvider;

    public function __construct(MenuTemplatesProvider $menuTemplatesProvider)
    {
        $this->menuTemplatesProvider = $menuTemplatesProvider;
    }

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
                    'image',
                    ImageType::class,
                    [
                        'label' => 'oro.commercemenu.menuupdate.image.label',
                        'tooltip' => 'oro.commercemenu.menuupdate.image.description',
                        'required' => false,
                    ]
                );

                $form->add('linkTarget', LinkTargetType::class, ['empty_data' => LinkTargetType::SAME_WINDOW_VALUE]);

                $form->add(
                    'menuTemplate',
                    ChoiceType::class,
                    [
                        'label' => 'oro.commercemenu.menuupdate.menu_template.label',
                        'tooltip' => 'oro.commercemenu.menuupdate.menu_template.description',
                        'required' => false,
                        'choices' => $this->getMenuTemplateChoices(),
                        'placeholder' => 'oro.commercemenu.menuupdate.menu_template.placeholder',
                    ]
                );
            }
        );
    }

    private function getMenuTemplateChoices(): array
    {
        $menuTemplates = $this->menuTemplatesProvider->getMenuTemplates();
        $menuTemplatesChoices = [];
        foreach ($menuTemplates as $menuTemplateKey => $menuTemplate) {
            $menuTemplatesChoices[$menuTemplate['label']] = $menuTemplateKey;
        }

        return $menuTemplatesChoices;
    }
}
