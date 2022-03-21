<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Provider;

use Oro\Bundle\FrontendBundle\Provider\ScreensProvider;
use Oro\Component\Layout\Extension\Theme\Model\Theme;
use Oro\Component\Layout\Extension\Theme\Model\ThemeManager;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class ScreensProviderTest extends \PHPUnit\Framework\TestCase
{
    private const SCREENS_CACHE_KEY = 'oro_frontend.provider.screens';

    private const SCREENS_CONFIG_1 = [
        'desktop' => [
            'label' => 'Sample desktop label',
            'hidingCssClass' => 'sample-desktop-class',
        ],
        'screen_to_be_overridden' => [
            'label' => 'Sample screen label',
            'hidingCssClass' => 'sample-screen-class',
        ],
    ];

    private const SCREENS_CONFIG_2 = [
        'mobile' => [
            'label' => 'Sample mobile label',
            'hidingCssClass' => 'sample-mobile-class',
        ],
        'screen_to_be_overridden' => [
            'label' => 'Sample overriden screen label',
            'hidingCssClass' => 'sample-overriden-screen-class',
        ],
    ];

    private const SCREENS_CONFIG_RESULT = [
        'desktop' => [
            'label' => 'Sample desktop label',
            'hidingCssClass' => 'sample-desktop-class',
        ],
        'mobile' => [
            'label' => 'Sample mobile label',
            'hidingCssClass' => 'sample-mobile-class',
        ],
        'screen_to_be_overridden' => [
            'label' => 'Sample overriden screen label',
            'hidingCssClass' => 'sample-overriden-screen-class',
        ],
    ];

    /** @var CacheInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $cache;

    /** @var ThemeManager|\PHPUnit\Framework\MockObject\MockObject */
    private $themeManager;

    /** @var ScreensProvider */
    private $screensProvider;

    protected function setUp(): void
    {
        $this->cache = $this->createMock(CacheInterface::class);
        $this->themeManager = $this->createMock(ThemeManager::class);
        $this->screensProvider = new ScreensProvider($this->themeManager, $this->cache);
    }

    public function testGetScreensWhenNoCache()
    {
        $this->cache->expects(self::once())
            ->method('get')
            ->with(self::SCREENS_CACHE_KEY)
            ->willReturnCallback(function ($cacheKey, $callback) {
                $item = $this->createMock(ItemInterface::class);
                return $callback($item);
            });

        $allThemes = [
            $this->getTheme(self::SCREENS_CONFIG_1),
            $this->getTheme(self::SCREENS_CONFIG_2),
        ];
        $this->themeManager->expects(self::once())
            ->method('getAllThemes')
            ->willReturn($allThemes);

        $screens = $this->screensProvider->getScreens();

        self::assertEquals(self::SCREENS_CONFIG_RESULT, $screens);
    }

    public function testGetScreensFromCache()
    {
        $this->cache->expects(self::once())
            ->method('get')
            ->with(self::SCREENS_CACHE_KEY)
            ->willReturn(self::SCREENS_CONFIG_RESULT);

        $screens = $this->screensProvider->getScreens();

        self::assertEquals(self::SCREENS_CONFIG_RESULT, $screens);
    }

    /**
     * @dataProvider getScreenDataProvider
     */
    public function testGetScreen(string $screenName, ?array $expectedScreen)
    {
        $this->cache->expects(self::atLeastOnce())
            ->method('get')
            ->with(self::SCREENS_CACHE_KEY)
            ->willReturn(self::SCREENS_CONFIG_RESULT);

        $screen = $this->screensProvider->getScreen($screenName);

        self::assertSame($expectedScreen, $screen);
    }

    public function getScreenDataProvider(): array
    {
        return [
            'existing screen' => [
                'screenName' => 'desktop',
                'expectedScreen' => self::SCREENS_CONFIG_RESULT['desktop'],
            ],
            'non-existing screen' => [
                'screenName' => 'non_existing',
                'expectedScreen' => null,
            ],
        ];
    }

    /**
     * @dataProvider hasScreenDataProvider
     */
    public function testHasScreen(string $screenName, bool $expectedResult)
    {
        $this->cache->expects(self::once())
            ->method('get')
            ->with(self::SCREENS_CACHE_KEY)
            ->willReturn(self::SCREENS_CONFIG_RESULT);

        $result = $this->screensProvider->hasScreen($screenName);

        self::assertSame($expectedResult, $result);
    }

    public function hasScreenDataProvider(): array
    {
        return [
            'existing screen' => [
                'screenName' => 'desktop',
                'expectedResult' => true,
            ],
            'non-existing screen' => [
                'screenName' => 'non_existing',
                'expectedResult' => false,
            ],
        ];
    }

    private function getTheme(array $screensConfig): Theme
    {
        $theme = $this->createMock(Theme::class);
        $theme->expects(self::once())
            ->method('getConfig')
            ->willReturn(['screens' => $screensConfig]);

        return $theme;
    }
}
