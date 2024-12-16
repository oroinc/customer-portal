<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Oro\Bundle\CatalogBundle\Form\Type\CategoryTreeType;
use Oro\Bundle\FormBundle\Utils\FormUtils;
use Oro\Bundle\NavigationBundle\Form\Type\RouteChoiceType;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Bundle\WebCatalogBundle\Entity\WebCatalog;
use Oro\Bundle\WebCatalogBundle\Form\Type\ContentNodeFromWebCatalogSelectType;
use Oro\Bundle\WebCatalogBundle\Form\Type\WebCatalogSelectType;
use Oro\Bundle\WebCatalogBundle\Provider\WebCatalogProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * Provides functionality for choosing a target page to redirect after logging in for the storefront.
 */
class RedirectAfterLoginConfigType extends AbstractType
{
    public const string TARGET_NONE = 'none';
    public const string TARGET_URI = 'uri';
    public const string TARGET_SYSTEM_PAGE = 'system_page';
    public const string TARGET_CONTENT_NODE = 'content_node';
    public const string TARGET_CATEGORY = 'category';

    private static array $fieldsByTargetTypes = [
        self::TARGET_URI => ['uri'],
        self::TARGET_CATEGORY => ['category'],
        self::TARGET_SYSTEM_PAGE => ['systemPageRoute'],
        self::TARGET_CONTENT_NODE => ['webCatalog', 'contentNode'],
    ];

    public function __construct(private WebCatalogProvider $webCatalogProvider)
    {
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'oro_redirect_after_login_config';
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    #[\Override]
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['attr']['data-page-component-module'] = 'oroui/js/app/components/view-component';
        $view->vars['attr']['data-page-component-options'] = json_encode([
            'view' => 'orowebcatalog/js/app/views/content-node-from-webcatalog-view',
            'listenedFieldName' => $view['webCatalog']->vars['full_name'],
            'triggeredFieldName' => $view['contentNode']->vars['full_name'],
        ], JSON_THROW_ON_ERROR);
    }

    public function onPreSetData(FormEvent $event): void
    {
        $data = $event->getData();
        $form = $event->getForm();

        $webCatalog = $this->getWebCatalog($data);

        $this->addTargetTypeField($form);
        $this->addUriField($form);
        $this->addSystemPageRouteField($form);
        $this->addCategoryField($form);
        $this->addContentNodeField($form, $webCatalog);
    }

    public function onPreSubmit(FormEvent $event): void
    {
        $data = $event->getData();
        if (!isset($data['targetType'])) {
            return;
        }

        foreach (self::$fieldsByTargetTypes as $targetType => $fields) {
            if ($data['targetType'] === $targetType) {
                continue;
            }

            foreach ($fields as $field) {
                FormUtils::replaceField($event->getForm(), $field, ['constraints' => []]);
            }
        }
    }

    public static function getTargetTypeChoices(): array
    {
        return [
            'oro.customer.form.target_type.content_node' => self::TARGET_CONTENT_NODE,
            'oro.customer.form.target_type.category' => self::TARGET_CATEGORY,
            'oro.customer.form.target_type.system_page' => self::TARGET_SYSTEM_PAGE,
            'oro.customer.form.target_type.uri' => self::TARGET_URI,
            'oro.customer.form.target_type.none' => self::TARGET_NONE,
        ];
    }

    private function addContentNodeField(FormInterface $form, ?WebCatalog $webCatalog): void
    {
        $form->add('webCatalog', WebCatalogSelectType::class, [
            'required' => true,
            'label' => false,
            'data' => $webCatalog,
            'mapped' => false,
            'auto_initialize' => false,
            'create_enabled' => false,
            'constraints' => [new NotBlank()],
        ]);

        $options = $webCatalog instanceof WebCatalog ? ['web_catalog' => $webCatalog] : [];
        $form->add('contentNode', ContentNodeFromWebCatalogSelectType::class, [
            'required' => true,
            'label' => false,
            'constraints' => [new NotBlank()],
             ...$options
        ]);
    }

    private function addCategoryField(FormInterface $form): void
    {
        $form->add('category', CategoryTreeType::class, [
            'required' => true,
            'label' => false,
            'constraints' => [new NotBlank()],
        ]);
    }

    private function addSystemPageRouteField(FormInterface $form): void
    {
        $form->add('systemPageRoute', RouteChoiceType::class, [
            'required' => true,
            'label' => false,
            'placeholder' => 'oro.customer.form.system_page_route.placeholder',
            'options_filter' => ['frontend' => true],
            'menu_name' => 'frontend_menu',
            'name_filter' => '/^oro_\w+(?<!frontend_root)$/',
            'constraints' => [new NotBlank()]
        ]);
    }

    private function addUriField(FormInterface $form): void
    {
        $form->add('uri', TextType::class, [
            'required' => true,
            'label' => false,
            'constraints' => [
                new NotBlank(),
                new Regex([
                    'pattern' => '/^(http|https)/',
                    'match' => false,
                    'message' => 'oro.customer.system_configuration.login_redirect.uri.relative_only'
                ])
            ]
        ]);
    }

    private function addTargetTypeField(FormInterface $form): void
    {
        $form->add('targetType', ChoiceType::class, [
            'required' => true,
            'label' => false,
            'choices' => self::getTargetTypeChoices(),
            'constraints' => [new NotBlank()],
        ]);
    }

    private function getWebCatalog(?array $data): ?WebCatalog
    {
        $node = $data['contentNode'] ?? null;

        return $node instanceof ContentNode ? $node->getWebCatalog() : $this->webCatalogProvider->getWebCatalog();
    }
}
