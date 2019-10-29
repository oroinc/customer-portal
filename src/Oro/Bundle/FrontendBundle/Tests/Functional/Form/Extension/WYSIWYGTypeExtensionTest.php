<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Form\Extension;

use Oro\Bundle\CMSBundle\Entity\Page;
use Oro\Bundle\FrontendBundle\Tests\Functional\Form\Extension\Stub\PageTypeStub;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class WYSIWYGTypeExtensionTest extends WebTestCase
{
    protected function setUp()
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

        $this->assertArrayHasKey('themes', $actualOptions);
        $this->assertIsArray($actualOptions['themes']);
        $this->assertEquals(
            [
                [
                    'name' => 'blank',
                    'label' => 'Blank theme',
                    'stylesheet' => '/layout-build/blank/css/styles.css'
                ],
                [
                    'name' => 'default',
                    'label' => 'Default theme',
                    'stylesheet' => '/layout-build/default/css/styles.css',
                    'active' => true
                ],
                [
                    'name' => 'custom',
                    'label' => 'Custom theme',
                    'stylesheet' => '/layout-build/custom/css/styles.css'
                ]
            ],
            $actualOptions['themes']
        );
    }
}
