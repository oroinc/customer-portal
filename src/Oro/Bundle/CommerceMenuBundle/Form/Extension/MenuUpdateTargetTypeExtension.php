<?php

namespace Oro\Bundle\CommerceMenuBundle\Form\Extension;

use Knp\Menu\ItemInterface;
use Oro\Bundle\CatalogBundle\Form\Type\CategoryTreeType;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\NavigationBundle\Entity\MenuUpdateInterface;
use Oro\Bundle\NavigationBundle\Form\Type\MenuUpdateType;
use Oro\Bundle\NavigationBundle\Form\Type\RouteChoiceType;
use Oro\Bundle\NavigationBundle\Menu\ConfigurationBuilder;
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
 * Adds menu item target fields to the {@see MenuUpdateType}.
 */
class MenuUpdateTargetTypeExtension extends AbstractTypeExtension
{
    private WebCatalogProvider $webCatalogProvider;

    private int $maxNestingLevel = 6;

    public function __construct(WebCatalogProvider $webCatalogProvider)
    {
        $this->webCatalogProvider = $webCatalogProvider;
    }

    public function setMaxNestingLevel(int $maxNestingLevel): void
    {
        $this->maxNestingLevel = $maxNestingLevel;
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

                /** @var ItemInterface|null $menuItem */
                $menuItem = $form->getConfig()->getOption('menu_item');
                $isRoot = (bool)$menuItem?->isRoot();
                $isDisabled = !$menuUpdate->isCustom() && !$isRoot;

                $this->addContentNodeField($menuUpdate, $form, $isDisabled);
                $this->addCategoryField($form, $isDisabled);
                $this->addMaxTraverseLevelField($form, $menuUpdate);
                $this->addSystemPageRouteField($form, $isDisabled);
                $this->addUriField($form, $isDisabled);
                $this->addTargetTypeField($form, $isDisabled, $menuItem);
            }
        );
    }

    private function addMaxTraverseLevelField(FormInterface $form, MenuUpdateInterface $menuUpdate): void
    {
        $menu = $form->getConfig()->getOption('menu');
        $menuItem = $form->getConfig()->getOption('menu_item');
        $maxNestingLevel = (int)$menu->getExtra(ConfigurationBuilder::MAX_NESTING_LEVEL, 0) ?: $this->maxNestingLevel;
        if ($menuItem?->isRoot()) {
            $allowedTraverseLevel = $maxNestingLevel;
            $minTraverseLevel = 1;
        } else {
            $minTraverseLevel = 0;
            $parentMenuItem = MenuUpdateUtils::findMenuItem($menu, $menuUpdate->getParentKey()) ?? $menu;
            $allowedTraverseLevel = max(0, $maxNestingLevel - $parentMenuItem->getLevel() - 1);

            if ($menuItem && !$menuUpdate->isCustom() && !$menuUpdate->isSynthetic()) {
                $parentMaxTraverseLevel = $parentMenuItem->getExtra(MenuUpdate::MAX_TRAVERSE_LEVEL);
                if ($parentMaxTraverseLevel !== null) {
                    $allowedTraverseLevel = min($allowedTraverseLevel, max(0, $parentMaxTraverseLevel - 1));
                }
            }
        }

        if ($menuUpdate->getMaxTraverseLevel() > $allowedTraverseLevel) {
            $menuUpdate->setMaxTraverseLevel($allowedTraverseLevel);
        }

        if ($menuUpdate->getMaxTraverseLevel() < $minTraverseLevel) {
            $menuUpdate->setMaxTraverseLevel($minTraverseLevel);
        }

        $choices = range($minTraverseLevel, $allowedTraverseLevel);

        $form->add(
            'maxTraverseLevel',
            ChoiceType::class,
            [
                'label' => 'oro.commercemenu.menuupdate.max_traverse_level.label',
                'tooltip' => 'oro.commercemenu.menuupdate.max_traverse_level.placeholder',
                'required' => false,
                'placeholder' => false,
                'choices' => array_combine($choices, $choices),
                'translatable_options' => false,
                'disabled' => $allowedTraverseLevel === 0,
            ]
        );
    }

    private function addContentNodeField(MenuUpdate $menuUpdate, FormInterface $form, bool $isDisabled): void
    {
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
                'disabled' => $isDisabled,
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
                    'disabled' => $isDisabled,
                    'error_bubbling' => false,
                ],
                $webCatalog instanceof WebCatalog ? ['web_catalog' => $webCatalog] : []
            )
        );
    }

    private function addCategoryField(FormInterface $form, bool $isDisabled): void
    {
        $form->add(
            'category',
            CategoryTreeType::class,
            [
                'required' => true,
                'label' => 'oro.commercemenu.menuupdate.category.label',
                'disabled' => $isDisabled,
                'error_bubbling' => false,
            ]
        );
    }

    private function addSystemPageRouteField(FormInterface $form, bool $isDisabled): void
    {
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
                'disabled' => $isDisabled,
                'error_bubbling' => false,
            ]
        );
    }

    private function addUriField(FormInterface $form, bool $isDisabled): void
    {
        $form->add(
            'uri',
            TextType::class,
            [
                'label' => 'oro.commercemenu.menuupdate.uri.label',
                'disabled' => $isDisabled,
                'required' => true,
                'empty_data' => '',
            ]
        );
    }

    private function addTargetTypeField(FormInterface $form, bool $isDisabled, ?ItemInterface $menuItem): void
    {
        $form->add(
            'targetType',
            ChoiceType::class,
            [
                'label' => 'oro.commercemenu.menuupdate.target_type.label',
                'required' => true,
                'multiple' => false,
                'choices' => $this->getTargetTypeChoices(),
                'disabled' => $isDisabled,
                'setter' => function (MenuUpdate $menuUpdate, ?string $targetType) {
                    $this->setTargetType($menuUpdate, $targetType);
                },
                'getter' => function (MenuUpdate $menuUpdate) use ($menuItem) {
                    return $this->getTargetType($menuUpdate, $menuItem);
                },
            ]
        );
    }

    private function getTargetTypeChoices(): array
    {
        return [
            'oro.commercemenu.menuupdate.target_type.content_node' => MenuUpdate::TARGET_CONTENT_NODE,
            'oro.commercemenu.menuupdate.target_type.category' => MenuUpdate::TARGET_CATEGORY,
            'oro.commercemenu.menuupdate.target_type.system_page' => MenuUpdate::TARGET_SYSTEM_PAGE,
            'oro.commercemenu.menuupdate.target_type.uri' => MenuUpdate::TARGET_URI,
            'oro.commercemenu.menuupdate.target_type.none' => MenuUpdate::TARGET_NONE,
        ];
    }

    private function setTargetType(MenuUpdate $menuUpdate, ?string $targetType): void
    {
        switch ($targetType) {
            case MenuUpdate::TARGET_CONTENT_NODE:
                $menuUpdate
                    ->setCategory(null)
                    ->setSystemPageRoute(null)
                    ->setUri(null);
                break;

            case MenuUpdate::TARGET_CATEGORY:
                $menuUpdate
                    ->setContentNode(null)
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

            case MenuUpdate::TARGET_NONE:
            default:
                $menuUpdate
                    ->setContentNode(null)
                    ->setCategory(null)
                    ->setSystemPageRoute(null)
                    ->setUri(null);
                break;
        }
    }

    private function getTargetType(MenuUpdate $menuUpdate, ?ItemInterface $menuItem): string
    {
        if ($menuUpdate->getContentNode() !== null) {
            return MenuUpdate::TARGET_CONTENT_NODE;
        }

        if ($menuUpdate->getCategory() !== null) {
            return MenuUpdate::TARGET_CATEGORY;
        }

        if ($menuUpdate->getSystemPageRoute()) {
            return MenuUpdate::TARGET_SYSTEM_PAGE;
        }

        if ($menuUpdate->getUri() !== null) {
            return MenuUpdate::TARGET_URI;
        }

        return $menuUpdate->getId() || $menuItem !== null ? MenuUpdate::TARGET_NONE : MenuUpdate::TARGET_URI;
    }
}
