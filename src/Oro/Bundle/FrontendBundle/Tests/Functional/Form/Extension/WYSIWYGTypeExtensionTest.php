<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Form\Extension;

use Oro\Bundle\FrontendBundle\Tests\Functional\Form\Extension\Stub\PageTypeStub;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class WYSIWYGTypeExtensionTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient();
    }

    public function testFinishView(): void
    {
        $container = $this->getContainer();

        $form = $container->get('form.factory')->create(PageTypeStub::class);
        $fieldView = $form->get('content')->createView();
        $actualOptions = json_decode($fieldView->vars['attr']['data-page-component-options'], \JSON_OBJECT_AS_ARRAY);

        $themeManager = $container->get('oro_layout.theme_manager');

        $this->assertArrayHasKey('themes', $actualOptions);
        $this->assertIsArray($actualOptions['themes']);
        $this->assertEquals($themeManager->getThemeNames(), array_keys($actualOptions['themes']));
    }
}
