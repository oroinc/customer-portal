<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Form\Type;

use Oro\Bundle\CatalogBundle\Form\Type\CategoryTreeType;
use Oro\Bundle\CustomerBundle\Form\Type\RedirectAfterLoginConfigType;
use Oro\Bundle\NavigationBundle\Form\Type\RouteChoiceType;
use Oro\Bundle\TestFrameworkBundle\Test\Form\FormAwareTestTrait;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Bundle\WebCatalogBundle\Entity\WebCatalog;
use Oro\Bundle\WebCatalogBundle\Form\Type\ContentNodeFromWebCatalogSelectType;
use Oro\Bundle\WebCatalogBundle\Form\Type\WebCatalogSelectType;
use Oro\Bundle\WebCatalogBundle\Tests\Functional\DataFixtures\LoadContentNodesData;
use Oro\Bundle\WebCatalogBundle\Tests\Functional\DataFixtures\LoadContentVariantScopes;
use Oro\Bundle\WebCatalogBundle\Tests\Functional\DataFixtures\LoadContentVariantsData;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RedirectAfterLoginConfigTypeTest extends WebTestCase
{
    use FormAwareTestTrait;

    private FormFactoryInterface $formFactory;

    #[\Override]
    protected function setUp(): void
    {
        $this->initClient();

        $this->formFactory = self::getContainer()->get(FormFactoryInterface::class);
    }

    public function testGetBlockPrefix(): void
    {
        $formType = $this->getContainer()->get('oro_customer.form.type.redirect_after_login_config');

        self::assertEquals('oro_redirect_after_login_config', $formType->getBlockPrefix());
    }

    public function testCreateFormWithoutData(): void
    {
        $form = $this->formFactory->create(RedirectAfterLoginConfigType::class);

        self::assertNull($form->getData());

        self::assertFormHasField($form, 'targetType', ChoiceType::class, [
            'required' => true,
            'label' => false,
            'choices' => RedirectAfterLoginConfigType::getTargetTypeChoices(),
            'constraints' => [new NotBlank()],
        ]);

        self::assertFormHasField($form, 'uri', TextType::class, [
            'required' => true,
            'label' => false,
            'constraints' => [
                new NotBlank(),
                new Regex([
                    'pattern' => '/^(http|https)/',
                    'match' => false,
                    'message' => 'oro.customer.system_configuration.login_redirect.uri.relative_only'
                ])
            ],
        ]);

        self::assertFormHasField($form, 'systemPageRoute', RouteChoiceType::class, [
            'required' => true,
            'label' => false,
            'placeholder' => 'oro.customer.form.system_page_route.placeholder',
            'options_filter' => ['frontend' => true],
            'menu_name' => 'frontend_menu',
            'name_filter' => '/^oro_\w+(?<!frontend_root)$/',
            'constraints' => [new NotBlank()]
        ]);

        self::assertFormHasField($form, 'category', CategoryTreeType::class, [
            'required' => true,
            'label' => false,
            'constraints' => [new NotBlank()],
        ]);

        self::assertFormHasField($form, 'webCatalog', WebCatalogSelectType::class, [
            'required' => true,
            'label' => false,
            'data' => null,
            'mapped' => false,
            'auto_initialize' => false,
            'create_enabled' => false,
            'constraints' => [new NotBlank()],
        ]);

        self::assertFormHasField($form, 'contentNode', ContentNodeFromWebCatalogSelectType::class, [
            'required' => true,
            'label' => false,
            'constraints' => [new NotBlank()],
        ]);
    }

    public function testCreateFormWithData(): void
    {
        $webCatalog = new WebCatalog();
        $contentNode = new ContentNode();
        $contentNode->setWebCatalog($webCatalog);
        $data = ['contentNode' => $contentNode];

        $form = $this->formFactory->create(RedirectAfterLoginConfigType::class, $data);

        self::assertEquals($data, $form->getData());

        self::assertFormHasField($form, 'webCatalog', WebCatalogSelectType::class, [
            'required' => true,
            'label' => false,
            'data' => $webCatalog,
            'mapped' => false,
            'auto_initialize' => false,
            'create_enabled' => false,
            'constraints' => [new NotBlank()],
        ]);

        self::assertFormHasField($form, 'contentNode', ContentNodeFromWebCatalogSelectType::class, [
            'required' => true,
            'label' => false,
            'constraints' => [new NotBlank()],
            'web_catalog' => $webCatalog,
        ]);
    }

    public function testHasViewVars(): void
    {
        $form = $this->formFactory->create(RedirectAfterLoginConfigType::class, null, ['csrf_protection' => false]);

        $formView = $form->createView();

        self::assertArrayHasKey('data-page-component-module', $formView->vars['attr']);
        self::assertEquals(
            'oroui/js/app/components/view-component',
            $formView->vars['attr']['data-page-component-module']
        );

        self::assertArrayHasKey('data-page-component-options', $formView->vars['attr']);
        $pageComponentOptions = json_decode($formView->vars['attr']['data-page-component-options'], true);

        self::assertEquals(
            [
                'view' => 'orowebcatalog/js/app/views/content-node-from-webcatalog-view',
                'listenedFieldName' => $formView['webCatalog']->vars['full_name'],
                'triggeredFieldName' => $formView['contentNode']->vars['full_name'],
            ],
            $pageComponentOptions
        );
    }

    public function testSubmitFormWithoutData(): void
    {
        $form = $this->formFactory->create(RedirectAfterLoginConfigType::class, null, ['csrf_protection' => false]);

        $form->submit([]);

        self::assertFalse($form->isValid());
        self::assertTrue($form->isSynchronized());
        self::assertCount(6, $form->getErrors(true));

        self::assertEquals(
            [
                'targetType' => null,
                'uri' => null,
                'systemPageRoute' => null,
                'category' => null,
                'contentNode' => null
            ],
            $form->getData()
        );
    }

    public function testSubmitFormWithInitialData(): void
    {
        $form = $this->formFactory->create(
            RedirectAfterLoginConfigType::class,
            ['targetType' => RedirectAfterLoginConfigType::TARGET_NONE],
            ['csrf_protection' => false]
        );

        $form->submit(['targetType' => RedirectAfterLoginConfigType::TARGET_NONE]);

        self::assertTrue($form->isValid());
        self::assertTrue($form->isSynchronized());
        self::assertCount(0, $form->getErrors(true));

        self::assertEquals(
            [
                'targetType' => RedirectAfterLoginConfigType::TARGET_NONE,
                'uri' => null,
                'systemPageRoute' => null,
                'category' => null,
                'contentNode' => null
            ],
            $form->getData()
        );
    }

    public function testSubmitFormWithData(): void
    {
        $this->loadFixtures([LoadContentVariantsData::class, LoadContentVariantScopes::class]);
        $contentNode = $this->getReference(LoadContentNodesData::CATALOG_1_ROOT_SUBNODE_2);

        $form = $this->formFactory->create(
            RedirectAfterLoginConfigType::class,
            [
                'targetType' => RedirectAfterLoginConfigType::TARGET_CONTENT_NODE,
                'contentNode' => $contentNode,
            ],
            ['csrf_protection' => false]
        );

        $form->submit([
            'targetType' => RedirectAfterLoginConfigType::TARGET_CONTENT_NODE,
            'contentNode' => $contentNode,
            'webCatalog' => $contentNode->getWebCatalog()
        ]);

        self::assertTrue($form->isValid());
        self::assertTrue($form->isSynchronized());
        self::assertCount(0, $form->getErrors(true));

        self::assertEquals(
            [
                'targetType' => RedirectAfterLoginConfigType::TARGET_CONTENT_NODE,
                'uri' => null,
                'systemPageRoute' => null,
                'category' => null,
                'contentNode' => $contentNode
            ],
            $form->getData()
        );
    }
}
