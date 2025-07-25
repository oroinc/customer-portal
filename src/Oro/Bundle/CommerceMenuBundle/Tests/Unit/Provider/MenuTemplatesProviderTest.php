<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Provider;

use Oro\Bundle\CommerceMenuBundle\Provider\MenuTemplatesProvider;
use Oro\Component\Layout\Extension\Theme\Model\Theme;
use Oro\Component\Layout\Extension\Theme\Model\ThemeManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class MenuTemplatesProviderTest extends TestCase
{
    private ThemeManager&MockObject $themeManager;
    private CacheInterface&MockObject $cache;
    private MenuTemplatesProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->themeManager = $this->createMock(ThemeManager::class);
        $this->cache = $this->createMock(CacheInterface::class);

        $this->provider = new MenuTemplatesProvider($this->themeManager, $this->cache);
    }

    public function testGetMenuTemplates(): void
    {
        $themeFoo = new Theme('foo');
        $themeFoo->setConfig(
            [
                'menu_templates' => [
                    'basic_menu_template' => [
                        'label' => 'Basic template foo theme',
                        'template' => 'basic_menu_template',
                    ],
                    'foo_theme_menu_template' => [
                        'label' => 'Foo template',
                        'template' => 'foo_theme_menu_template',
                    ],
                ]
            ]
        );

        $themeBar = new Theme('bar');
        $themeBar->setConfig(
            [
                'menu_templates' => [
                    'basic_menu_template' => [
                        'label' => 'Basic template bar theme',
                        'template' => 'basic_menu_template',
                    ],
                    'bar_theme_menu' => [
                        'label' => 'Bar template',
                        'template' => 'bar_theme_menu',
                    ],
                ]
            ]
        );

        $this->themeManager->expects(self::once())
            ->method('getAllThemes')
            ->willReturn([$themeFoo, $themeBar]);

        $this->cache->expects(self::once())
            ->method('get')
            ->with('commerce_menu_templates')
            ->willReturnCallback(function ($cacheKey, $callback) {
                $item = $this->createMock(ItemInterface::class);

                return $callback($item);
            });

        self::assertEquals(
            [
                'basic_menu_template' => [
                    'label' => 'Basic template bar theme',
                    'template' => 'basic_menu_template',
                ],
                'foo_theme_menu_template' => [
                    'label' => 'Foo template',
                    'template' => 'foo_theme_menu_template',
                ],
                'bar_theme_menu' => [
                    'label' => 'Bar template',
                    'template' => 'bar_theme_menu',
                ],
            ],
            $this->provider->getMenuTemplates()
        );
    }

    public function testGetMenuTemplatesFromCache(): void
    {
        $cachedMenuTemplates = [
            'basic_menu_template' => [
                'label' => 'Basic template bar theme',
                'template' => 'basic_menu_template',
            ],
            'foo_theme_menu_template' => [
                'label' => 'Foo template',
                'template' => 'foo_theme_menu_template',
            ],
            'bar_theme_menu' => [
                'label' => 'Bar template',
                'template' => 'bar_theme_menu',
            ],
        ];

        $this->cache->expects(self::once())
            ->method('get')
            ->with('commerce_menu_templates')
            ->willReturn($cachedMenuTemplates);

        $this->themeManager->expects(self::never())
            ->method('getAllThemes');

        self::assertEquals($cachedMenuTemplates, $this->provider->getMenuTemplates());
    }
}
