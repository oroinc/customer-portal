<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Form\Type;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;
use Oro\Bundle\FrontendBundle\Form\Type\QuickAccessButtonConfigType;
use Oro\Bundle\FrontendBundle\Model\QuickAccessButtonConfig;
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

        $this->formFactory = $this->getContainer()->get('form.factory');
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

        $nodeId = $this->getReference(LoadWebCatalogWithContentNodes::CONTENT_NODE_1)->getId();
        $submitData = [
            'type' => 'menu',
            'menu' => 'commerce_main_menu',
            'webCatalogNode' => $nodeId,
        ];
        $isValid = true;
        $expectedData = (new QuickAccessButtonConfig())
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
        $submitData = [
            'type' => 'menu',
            'menu' => 'commerce_main_menu',
        ];
        $isValid = true;
        $expectedData = (new QuickAccessButtonConfig())
            ->setType('menu')
            ->setWebCatalogNode(null)
            ->setMenu('commerce_main_menu');

        $form = $this->formFactory->create(QuickAccessButtonConfigType::class, null, [
            'csrf_protection' => false,
        ]);
        self::assertFalse($form->has('web_catalog_node'));
        self::assertEquals([
            'menu' => 'menu',
        ], $form->get('type')->getConfig()->getOption('choices'));
        $form->submit($submitData);

        self::assertEquals($isValid, $form->isValid(), (string) $form->getErrors(true));
        self::assertTrue($form->isSynchronized(), $form->getTransformationFailure()?->getMessage() ?? '');
        self::assertEquals($expectedData, $form->getData());
    }
}
