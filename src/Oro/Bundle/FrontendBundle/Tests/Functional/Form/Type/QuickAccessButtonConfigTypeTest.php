<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Form\Type;

use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;
use Oro\Bundle\FrontendBundle\Form\Type\QuickAccessButtonConfigType;
use Oro\Bundle\FrontendBundle\Model\QuickAccessButtonConfig;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Model\FallbackType;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;
use Oro\Bundle\WebCatalogBundle\Tests\Functional\DataFixtures\LoadWebCatalogWithContentNodes;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * @dbIsolationPerTest
 */
class QuickAccessButtonConfigTypeTest extends WebTestCase
{
    use ConfigManagerAwareTestTrait;

    private ?int $initialWebCatalogId;
    private FormFactoryInterface $formFactory;

    #[\Override]
    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([
            LoadUser::class,
            LoadWebCatalogWithContentNodes::class
        ]);

        $this->initialWebCatalogId = self::getConfigManager()->get('oro_web_catalog.web_catalog');

        $this->formFactory = self::getContainer()->get('form.factory');
    }

    #[\Override]
    protected function tearDown(): void
    {
        if (false !== $this->initialWebCatalogId) {
            $configManager = self::getConfigManager();
            $configManager->set('oro_web_catalog.web_catalog', $this->initialWebCatalogId);
            $configManager->flush();
        }
    }

    public function testSubmitIfMenuSelected(): void
    {
        $webCatalog = $this->getReference(LoadWebCatalogWithContentNodes::WEB_CATALOG_NAME);
        $configManager = self::getConfigManager();
        $configManager->set('oro_web_catalog.web_catalog', $webCatalog->getId());
        $configManager->flush();

        $localizations = $this->getClientContainer()
            ->get('doctrine')
            ->getRepository(Localization::class)
            ->findAllIndexedById();

        $labelData = ['default' => 'test'];
        /** @var Localization $localization */
        foreach ($localizations as $localization) {
            $labelData['localizations'][$localization->getId()] = ['use_fallback' => 1, 'fallback' => 'system'];
        }

        $nodeId = $this->getReference(LoadWebCatalogWithContentNodes::CONTENT_NODE_1)->getId();
        $submitData = [
            'label' => $labelData,
            'type' => QuickAccessButtonConfig::TYPE_MENU,
            'menu' => 'commerce_main_menu',
            'webCatalogNode' => $nodeId,
        ];
        $expectedLabelData = ['' => 'test'];
        /** @var Localization $localization */
        foreach ($localizations as $localization) {
            $expectedLabelData[$localization->getId()] = new FallbackType('system');
        }
        $expectedData = (new QuickAccessButtonConfig())
            ->setLabel($expectedLabelData)
            ->setType(QuickAccessButtonConfig::TYPE_MENU)
            ->setMenu('commerce_main_menu');

        $form = $this->formFactory->create(QuickAccessButtonConfigType::class, null, [
            'csrf_protection' => false,
        ]);

        $form->submit($submitData);

        self::assertTrue($form->isValid(), (string) $form->getErrors(true));
        self::assertTrue($form->isSynchronized(), $form->getTransformationFailure()?->getMessage() ?? '');
        self::assertTrue($form->get('label')->getConfig()->getOption('required'));
        self::assertNotEmpty($form->get('label')->getConfig()->getOption('entry_options'));
        self::assertEquals($expectedData, $form->getData());
    }

    public function testSubmitIfTypeWebCatalogSelected(): void
    {
        $webCatalog = $this->getReference(LoadWebCatalogWithContentNodes::WEB_CATALOG_NAME);
        $configManager = self::getConfigManager();
        $configManager->set('oro_web_catalog.web_catalog', $webCatalog->getId());
        $configManager->flush();

        $localizations = $this->getClientContainer()
            ->get('doctrine')
            ->getRepository(Localization::class)
            ->findAllIndexedById();

        $labelData = ['default' => 'test'];
        /** @var Localization $localization */
        foreach ($localizations as $localization) {
            $labelData['localizations'][$localization->getId()] = ['use_fallback' => 1, 'fallback' => 'system'];
        }

        $nodeId = $this->getReference(LoadWebCatalogWithContentNodes::CONTENT_NODE_1)->getId();
        $submitData = [
            'label' => $labelData,
            'type' => QuickAccessButtonConfig::TYPE_WEB_CATALOG_NODE,
            'menu' => 'commerce_main_menu',
            'webCatalogNode' => $nodeId,
        ];
        $expectedLabelData = ['' => 'test'];
        /** @var Localization $localization */
        foreach ($localizations as $localization) {
            $expectedLabelData[$localization->getId()] = new FallbackType('system');
        }
        $expectedData = (new QuickAccessButtonConfig())
            ->setLabel($expectedLabelData)
            ->setType(QuickAccessButtonConfig::TYPE_WEB_CATALOG_NODE)
            ->setWebCatalogNode($nodeId);

        $form = $this->formFactory->create(QuickAccessButtonConfigType::class, null, [
            'csrf_protection' => false,
        ]);

        $form->submit($submitData);

        self::assertTrue($form->isValid(), (string) $form->getErrors(true));
        self::assertTrue($form->isSynchronized(), $form->getTransformationFailure()?->getMessage() ?? '');
        self::assertTrue($form->get('label')->getConfig()->getOption('required'));
        self::assertNotEmpty($form->get('label')->getConfig()->getOption('entry_options'));
        self::assertEquals($expectedData, $form->getData());
    }

    public function testSubmitIfTypeNoneSelected(): void
    {
        $webCatalog = $this->getReference(LoadWebCatalogWithContentNodes::WEB_CATALOG_NAME);
        $configManager = self::getConfigManager();
        $configManager->set('oro_web_catalog.web_catalog', $webCatalog->getId());
        $configManager->flush();

        $nodeId = $this->getReference(LoadWebCatalogWithContentNodes::CONTENT_NODE_1)->getId();
        $submitData = [
            'label' => ['default' => 'test'],
            'type' => '',
            'menu' => 'commerce_main_menu',
            'webCatalogNode' => $nodeId,
        ];

        $form = $this->formFactory->create(QuickAccessButtonConfigType::class, null, [
            'csrf_protection' => false,
        ]);
        $form->submit($submitData);

        self::assertTrue($form->isValid(), (string) $form->getErrors(true));
        self::assertTrue($form->isSynchronized(), $form->getTransformationFailure()?->getMessage() ?? '');
        self::assertTrue($form->get('label')->getConfig()->getOption('required'));
        self::assertEmpty($form->get('label')->getConfig()->getOption('entry_options'));
        self::assertEquals(new QuickAccessButtonConfig(), $form->getData());
    }

    public function testSubmitIfNoWebCatalogSelected(): void
    {
        $localizations = $this->getClientContainer()
            ->get('doctrine')
            ->getRepository(Localization::class)
            ->findAllIndexedById();

        $labelData = ['default' => 'test'];
        /** @var Localization $localization */
        foreach ($localizations as $localization) {
            $labelData['localizations'][$localization->getId()] = ['use_fallback' => 1, 'fallback' => 'system'];
        }

        $submitData = [
            'label' => $labelData,
            'type' => QuickAccessButtonConfig::TYPE_MENU,
            'menu' => 'commerce_main_menu',
        ];

        $expectedLabelData = ['' => 'test'];
        /** @var Localization $localization */
        foreach ($localizations as $localization) {
            $expectedLabelData[$localization->getId()] = new FallbackType('system');
        }
        $expectedData = (new QuickAccessButtonConfig())
            ->setLabel($expectedLabelData)
            ->setType(QuickAccessButtonConfig::TYPE_MENU)
            ->setWebCatalogNode(null)
            ->setMenu('commerce_main_menu');

        $form = $this->formFactory->create(QuickAccessButtonConfigType::class, null, [
            'csrf_protection' => false,
        ]);
        self::assertFalse($form->has('web_catalog_node'));
        self::assertEquals([
            'oro_frontend.form.quick_access_button.fields.type.choices.menu' => 'menu',
        ], $form->get('type')->getConfig()->getOption('choices'));

        $form->submit($submitData);

        self::assertTrue($form->isValid(), (string) $form->getErrors(true));
        self::assertTrue($form->isSynchronized(), $form->getTransformationFailure()?->getMessage() ?? '');
        self::assertTrue($form->get('label')->getConfig()->getOption('required'));
        self::assertNotEmpty($form->get('label')->getConfig()->getOption('entry_options'));
        self::assertEquals($expectedData, $form->getData());
    }

    public function testSubmitValidationError(): void
    {
        $submitData = [
            'label' => '',
            'type' => QuickAccessButtonConfig::TYPE_MENU,
            'menu' => 'commerce_main_menu',
        ];

        $expectedData = (new QuickAccessButtonConfig())
            ->setType(QuickAccessButtonConfig::TYPE_MENU)
            ->setMenu('commerce_main_menu');

        $form = $this->formFactory->create(QuickAccessButtonConfigType::class, null, [
            'csrf_protection' => false,
        ]);

        $form->submit($submitData);

        self::assertFalse($form->isValid());
        self::assertCount(1, $form->getErrors(true));
        self::assertEquals('This value is not valid.', $form->getErrors(true)[0]->getMessage());
        self::assertTrue($form->isSynchronized(), $form->getTransformationFailure()?->getMessage() ?? '');
        self::assertTrue($form->get('label')->getConfig()->getOption('required'));
        self::assertNotEmpty($form->get('label')->getConfig()->getOption('entry_options'));
        self::assertEquals($expectedData, $form->getData());
    }
}
