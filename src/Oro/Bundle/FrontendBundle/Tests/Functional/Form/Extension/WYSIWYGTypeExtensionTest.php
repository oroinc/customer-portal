<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Form\Extension;

use Oro\Bundle\CMSBundle\Entity\Page;
use Oro\Bundle\FrontendBundle\Tests\Functional\Form\Extension\Stub\PageTypeStub;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class WYSIWYGTypeExtensionTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
        $this->updateUserSecurityToken(self::AUTH_USER);
    }

    public function testFinishView(): void
    {
        $container = $this->getContainer();

        $form = $container->get('form.factory')->create(PageTypeStub::class, null, ['data_class' => Page::class]);
        $fieldView = $form->get('content')->createView();
        $actualOptions = json_decode($fieldView->vars['attr']['data-page-component-options'], \JSON_OBJECT_AS_ARRAY);

        $layoutThemeName = $container->get('oro_config.manager')->get('oro_frontend.frontend_theme');

        $this->assertArrayHasKey('themes', $actualOptions);
        $this->assertIsArray($actualOptions['themes']);
        $this->assertContains([
            'name' => 'blank',
            'label' => 'Blank theme',
            'stylesheet' => '/layout-build/blank/css/styles.css'
        ], $actualOptions['themes']);
        $defaultTheme = [
            'name' => 'default',
            'label' => 'Default theme',
            'stylesheet' => '/layout-build/default/css/styles.css',
        ];
        if ($layoutThemeName === 'default') {
            $defaultTheme['active'] = true;
        }
        $this->assertContains($defaultTheme, $actualOptions['themes']);
        $this->assertContains([
            'name' => 'custom',
            'label' => 'Custom theme',
            'stylesheet' => '/layout-build/custom/css/styles.css',
        ], $actualOptions['themes']);
    }
}
