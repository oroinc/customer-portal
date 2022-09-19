<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Form\Extension;

use Oro\Bundle\CMSBundle\Entity\Page;
use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;
use Oro\Bundle\FrontendBundle\Tests\Functional\Form\Extension\Stub\PageTypeStub;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class WYSIWYGTypeExtensionTest extends WebTestCase
{
    use ConfigManagerAwareTestTrait;

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
        $actualOptions = json_decode(
            $fieldView->vars['attr']['data-page-component-options'],
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $layoutThemeName = self::getConfigManager(null)->get('oro_frontend.frontend_theme');

        $this->assertArrayHasKey('themes', $actualOptions);
        $this->assertIsArray($actualOptions['themes']);

        $defaultTheme = [
            'name' => 'default',
            'label' => 'Default theme',
            'stylesheet' => '/build/default/css/styles.css',
        ];
        if ($layoutThemeName === 'default') {
            $defaultTheme['active'] = true;
        }
        $this->assertThemeOptions($defaultTheme, $actualOptions['themes']);

        $this->assertThemeOptions([
            'name' => 'custom',
            'label' => 'Custom theme',
            'stylesheet' => '/build/custom/css/styles.css',
        ], $actualOptions['themes']);
    }

    /**
     * @param array $themeOptions
     * @param array $actualThemesOptions
     */
    private function assertThemeOptions(array $themeOptions, array $actualThemesOptions): void
    {
        $hasThemeOptions = false;
        foreach ($actualThemesOptions as $actualThemeOptions) {
            if (($actualThemeOptions['name'] ?? null) === $themeOptions['name']) {
                $hasThemeOptions = true;
                break;
            }
        }
        $this->assertTrue($hasThemeOptions, sprintf("Theme's '%s' options are missing", $themeOptions['name']));
        $this->assertEquals($themeOptions['label'], $actualThemeOptions['label'] ?? null);
        $this->assertMatchesRegularExpression(
            '/^' . preg_quote($themeOptions['stylesheet'], '/') . '(\?|$)/',
            $actualThemeOptions['stylesheet'] ?? ''
        );
        $this->assertEquals($themeOptions['active'] ?? null, $actualThemeOptions['active'] ?? null);
    }
}
