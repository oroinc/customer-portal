<?php

namespace Oro\Bundle\CommerceMenuBundle\Form\Extension;

use Knp\Menu\ItemInterface;
use Oro\Bundle\AttachmentBundle\Form\Type\ImageType;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\CommerceMenuBundle\Form\Type\MenuScreensConditionType;
use Oro\Bundle\CommerceMenuBundle\Form\Type\MenuUserAgentConditionsCollectionType;
use Oro\Bundle\FormBundle\Form\Type\LinkTargetType;
use Oro\Bundle\NavigationBundle\Form\Type\MenuUpdateType;
use Oro\Bundle\NavigationBundle\Form\Type\RouteChoiceType;
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

/**
 * Add fields related to CommerceMenuBundle MenuUpdate entity.
 */
class MenuUpdateExtension extends AbstractTypeExtension
{
    /** @var WebCatalogProvider */
    private $webCatalogProvider;

    public function __construct(WebCatalogProvider $webCatalogProvider)
    {
        $this->webCatalogProvider = $webCatalogProvider;
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
                $menuItem = $form->getConfig()->getOption('menu_item');
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
                    LinkTargetType::class,
                    [
                        'empty_data' => LinkTargetType::SAME_WINDOW_VALUE,
                        'getter' => function (MenuUpdate $menuUpdate) use ($menuItem) {
                            return $this->getLinkTarget($menuUpdate, $menuItem);
                        },
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
                    'tooltip' => 'oro.commercemenu.form.tooltip.menu_item_condition',
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
        $menuUpdate = $event->getData();

        if (false === $menuUpdate->isCustom()) {
            return;
        }

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
                    'oro.commercemenu.menuupdate.target_type.system_page' => MenuUpdate::TARGET_SYSTEM_PAGE,
                    'oro.commercemenu.menuupdate.target_type.uri' => MenuUpdate::TARGET_URI,
                ],
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
            ]
        );

        $form->add(
            'contentNode',
            ContentNodeFromWebCatalogSelectType::class,
            array_merge(
                [
                    'label' => 'oro.commercemenu.menuupdate.content_node.label',
                    'required' => true,
                ],
                $webCatalog instanceof WebCatalog ? ['web_catalog' => $webCatalog] : []
            )
        );

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
                    ->setUri(null)
                    ->setSystemPageRoute(null);
                break;

            case MenuUpdate::TARGET_SYSTEM_PAGE:
                $menuUpdate
                    ->setContentNode(null)
                    ->setUri(null);
                break;

            case MenuUpdate::TARGET_URI:
                $menuUpdate
                    ->setContentNode(null)
                    ->setSystemPageRoute(null);
                break;
        }
    }

    private function getLinkTarget(MenuUpdate $menuUpdate, ?ItemInterface $menuItem): int
    {
        if ($menuUpdate->getId()) {
            return $menuUpdate->getLinkTarget();
        }

        return $menuItem && $menuItem->getLinkAttribute('target') === '_blank'
            ? LinkTargetType::NEW_WINDOW_VALUE
            : LinkTargetType::SAME_WINDOW_VALUE;
    }

    /**
     * {@inheritDoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [MenuUpdateType::class];
    }
}
