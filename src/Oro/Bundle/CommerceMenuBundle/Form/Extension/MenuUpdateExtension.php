<?php

namespace Oro\Bundle\CommerceMenuBundle\Form\Extension;

use Oro\Bundle\AttachmentBundle\Form\Type\ImageType;
use Oro\Bundle\CatalogBundle\Form\Type\CategoryTreeType;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\CommerceMenuBundle\Form\Type\MenuScreensConditionType;
use Oro\Bundle\CommerceMenuBundle\Form\Type\MenuUserAgentConditionsCollectionType;
use Oro\Bundle\CommerceMenuBundle\Provider\MenuTemplatesProvider;
use Oro\Bundle\FormBundle\Form\Type\LinkTargetType;
use Oro\Bundle\NavigationBundle\Entity\MenuUpdateInterface;
use Oro\Bundle\NavigationBundle\Form\Type\MenuUpdateType;
use Oro\Bundle\NavigationBundle\Form\Type\RouteChoiceType;
use Oro\Bundle\NavigationBundle\Utils\MenuUpdateUtils;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Bundle\WebCatalogBundle\Entity\WebCatalog;
use Oro\Bundle\WebCatalogBundle\Form\Type\ContentNodeFromWebCatalogSelectType;
use Oro\Bundle\WebCatalogBundle\Form\Type\WebCatalogSelectType;
use Oro\Bundle\WebCatalogBundle\Provider\WebCatalogProvider;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

/**
 * Adds fields related to CommerceMenuBundle MenuUpdate entity.
 */
class MenuUpdateExtension extends AbstractTypeExtension
{
    private WebCatalogProvider $webCatalogProvider;

    private MenuTemplatesProvider $menuTemplatesProvider;

    public function __construct(WebCatalogProvider $webCatalogProvider, MenuTemplatesProvider $menuTemplatesProvider)
    {
        $this->webCatalogProvider = $webCatalogProvider;
        $this->menuTemplatesProvider = $menuTemplatesProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $data = $event->getData();
                if (!$data instanceof MenuUpdate) {
                    return;
                }

                $form = $event->getForm();
                $form->add(
                    'image',
                    ImageType::class,
                    [
                        'label' => 'oro.commercemenu.menuupdate.image.label',
                        'required' => false,
                    ]
                );

                $this->addConditionalFields($event);
                $this->addTargetFields($event);

                $form->add(
                    'linkTarget',
                    LinkTargetType::class
                );

                $form->add(
                    'menuTemplate',
                    ChoiceType::class,
                    [
                        'label' => 'oro.commercemenu.menuupdate.menu_template.label',
                        'required' => false,
                        'choices' => $this->getMenuTemplateChoices(),
                        'placeholder' => 'oro.commercemenu.menuupdate.menu_template.placeholder',
                    ]
                );
            }
        );

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'clearTargetFields']);
    }

    private function addConditionalFields(FormEvent $event): void
    {
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

    private function addTargetFields(FormEvent $event): void
    {
        $form = $event->getForm();
        /** @var MenuUpdate $menuUpdate */
        $menuUpdate = $event->getData();

        $form->add(
            'targetType',
            ChoiceType::class,
            [
                'label' => 'oro.commercemenu.menuupdate.target_type.label',
                'required' => true,
                'data' => $menuUpdate->getTargetType(),
                'mapped' => false,
                'multiple' => false,
                'choices' => [
                    'oro.commercemenu.menuupdate.target_type.content_node' => MenuUpdate::TARGET_CONTENT_NODE,
                    'oro.commercemenu.menuupdate.target_type.category' => MenuUpdate::TARGET_CATEGORY,
                    'oro.commercemenu.menuupdate.target_type.system_page' => MenuUpdate::TARGET_SYSTEM_PAGE,
                    'oro.commercemenu.menuupdate.target_type.uri' => MenuUpdate::TARGET_URI,
                ],
                'disabled' => !$menuUpdate->isCustom(),
            ]
        );

        $contentNode = $menuUpdate->getContentNode();
        $webCatalog = $contentNode instanceof ContentNode
            ? $contentNode->getWebCatalog()
            : $this->webCatalogProvider->getWebCatalog();

        $form->add(
            'webCatalog',
            WebCatalogSelectType::class,
            [
                'required' => true,
                'label' => 'oro.commercemenu.menuupdate.webcatalog.label',
                'data' => $webCatalog,
                'tooltip' => 'oro.commercemenu.menuupdate.webcatalog.description',
                'mapped' => false,
                'auto_initialize' => false,
                'create_enabled' => false,
                'disabled' => !$menuUpdate->isCustom(),
                'error_bubbling' => false,
            ]
        );

        $form->add(
            'contentNode',
            ContentNodeFromWebCatalogSelectType::class,
            array_merge(
                [
                    'label' => 'oro.commercemenu.menuupdate.content_node.label',
                    'required' => true,
                    'disabled' => !$menuUpdate->isCustom(),
                    'error_bubbling' => false,
                ],
                $webCatalog instanceof WebCatalog ? ['web_catalog' => $webCatalog] : []
            )
        );

        $form->add(
            'category',
            CategoryTreeType::class,
            [
                'required' => true,
                'label' => 'oro.commercemenu.menuupdate.category.label',
                'disabled' => !$menuUpdate->isCustom(),
                'error_bubbling' => false,
            ]
        );

        $this->addMaxTraverseLevelField($form, $menuUpdate);

        $form->add(
            'systemPageRoute',
            RouteChoiceType::class,
            [
                'required' => true,
                'label' => 'oro.commercemenu.menuupdate.system_page_route.label',
                'placeholder' => 'oro.commercemenu.menuupdate.system_page_route.placeholder',
                'options_filter' => [
                    'frontend' => true,
                ],
                'menu_name' => 'frontend_menu',
                'disabled' => !$menuUpdate->isCustom(),
                'error_bubbling' => false,
            ]
        );
    }

    private function addMaxTraverseLevelField(FormInterface $form, MenuUpdateInterface $menuUpdate): void
    {
        $menu = $form->getConfig()->getOption('menu');
        $parentMenuItem = MenuUpdateUtils::findMenuItem($menu, $menuUpdate->getParentKey()) ?? $menu;
        $maxTraverseLevel = max(0, MenuUpdateUtils::getAllowedNestingLevel($parentMenuItem) - 1);
        if ($menuUpdate->getMaxTraverseLevel() > $maxTraverseLevel) {
            $menuUpdate->setMaxTraverseLevel($maxTraverseLevel);
        }

        $form->add(
            'maxTraverseLevel',
            ChoiceType::class,
            [
                'label' => 'oro.commercemenu.menuupdate.max_traverse_level.label',
                'tooltip' => 'oro.commercemenu.menuupdate.max_traverse_level.placeholder',
                'required' => false,
                'placeholder' => false,
                'choices' => range(0, $maxTraverseLevel),
                'translatable_options' => false,
                'disabled' => $maxTraverseLevel === 0,
            ]
        );
    }

    public function clearTargetFields(FormEvent $event): void
    {
        /** @var MenuUpdate $menuUpdate */
        $menuUpdate = $event->getData();

        if (!$menuUpdate instanceof MenuUpdate) {
            return;
        }

        if (false === $menuUpdate->isCustom()) {
            return;
        }

        $targetType = $event->getForm()['targetType']->getData();
        switch ($targetType) {
            case MenuUpdate::TARGET_CONTENT_NODE:
                $menuUpdate
                    ->setCategory(null)
                    ->setSystemPageRoute(null)
                    ->setUri(null);
                break;

            case MenuUpdate::TARGET_SYSTEM_PAGE:
                $menuUpdate
                    ->setContentNode(null)
                    ->setCategory(null)
                    ->setUri(null);
                break;

            case MenuUpdate::TARGET_URI:
                $menuUpdate
                    ->setContentNode(null)
                    ->setCategory(null)
                    ->setSystemPageRoute(null);
                break;

            case MenuUpdate::TARGET_CATEGORY:
                $menuUpdate
                    ->setContentNode(null)
                    ->setSystemPageRoute(null)
                    ->setUri(null);
                break;
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [MenuUpdateType::class];
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
