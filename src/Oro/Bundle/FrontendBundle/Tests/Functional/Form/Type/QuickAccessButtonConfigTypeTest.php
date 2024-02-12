<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Form\Type;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
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

    private FormFactoryInterface $formFactory;
    private ConfigManager $configManager;

    protected function setUp(): void
    {
        $this->initClient();

        $this->formFactory = self::getContainer()->get('form.factory');
        $this->loadFixtures([
            LoadUser::class,
            LoadWebCatalogWithContentNodes::class,
        ]);

        $this->configManager = self::getConfigManager('global');
    }

    protected function tearDown(): void
    {
        $config = self::getConfigManager();
        $config->reset('oro_web_catalog.web_catalog');
        parent::tearDown();
    }

    public function testSubmit(): void
    {
        $webCatalog = $this->getReference(LoadWebCatalogWithContentNodes::WEB_CATALOG_NAME);
        $this->configManager->set('oro_web_catalog.web_catalog', $webCatalog->getId());
        $this->configManager->flush();

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
            'type' => 'menu',
            'menu' => 'commerce_main_menu',
            'webCatalogNode' => $nodeId,
        ];
        $isValid = true;
        $expectedLabelData = ['' => 'test'];
        /** @var Localization $localization */
        foreach ($localizations as $localization) {
            $expectedLabelData[$localization->getId()] = new FallbackType('system');
        }
        $expectedData = (new QuickAccessButtonConfig())
            ->setLabel($expectedLabelData)
            ->setType('menu')
            ->setWebCatalogNode($nodeId)
            ->setMenu('commerce_main_menu');

        $form = $this->formFactory->create(QuickAccessButtonConfigType::class, null, [
            'csrf_protection' => false,
        ]);
        $form->submit($submitData);

        self::assertEquals($isValid, $form->isValid(), (string) $form->getErrors(true));
        self::assertTrue($form->isSynchronized(), $form->getTransformationFailure()?->getMessage() ?? '');
        self::assertEquals($expectedData, $form->getData());
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
            'type' => 'menu',
            'menu' => 'commerce_main_menu',
        ];
        $isValid = true;

        $expectedLabelData = ['' => 'test'];
        /** @var Localization $localization */
        foreach ($localizations as $localization) {
            $expectedLabelData[$localization->getId()] = new FallbackType('system');
        }
        $expectedData = (new QuickAccessButtonConfig())
            ->setLabel($expectedLabelData)
            ->setType('menu')
            ->setWebCatalogNode(null)
            ->setMenu('commerce_main_menu');

        $form = $this->formFactory->create(QuickAccessButtonConfigType::class, null, [
            'csrf_protection' => false,
        ]);
        self::assertFalse($form->has('web_catalog_node'));
        self::assertEquals([
            'oro_frontend.system_configuration.fields.quick_access_button.fields.type.choices.menu' => 'menu',
        ], $form->get('type')->getConfig()->getOption('choices'));
        $form->submit($submitData);

        self::assertEquals($isValid, $form->isValid(), (string) $form->getErrors(true));
        self::assertTrue($form->isSynchronized(), $form->getTransformationFailure()?->getMessage() ?? '');
        self::assertEquals($expectedData, $form->getData());
    }
}
