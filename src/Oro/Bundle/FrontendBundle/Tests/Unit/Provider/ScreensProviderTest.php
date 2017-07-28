<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Provider;

use Doctrine\Common\Cache\ChainCache;
use Oro\Bundle\FrontendBundle\Provider\ScreensProvider;
use Oro\Component\Layout\Extension\Theme\Model\Theme;
use Oro\Component\Layout\Extension\Theme\Model\ThemeManager;

class ScreensProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @internal
     */
    const SCREENS_CACHE_KEY = 'oro_frontend.provider.screens';

    /**
     * @internal
     */
    const SCREENS_CONFIG_1 = [
        'desktop' => [
            'label' => 'Sample desktop label',
            'hidingCssClass' => 'sample-desktop-class',
        ],
        'screen_to_be_overridden' => [
            'label' => 'Sample screen label',
            'hidingCssClass' => 'sample-screen-class',
        ],
    ];

    /**
     * @internal
     */
    const SCREENS_CONFIG_2 = [
        'mobile' => [
            'label' => 'Sample mobile label',
            'hidingCssClass' => 'sample-mobile-class',
        ],
        'screen_to_be_overridden' => [
            'label' => 'Sample overriden screen label',
            'hidingCssClass' => 'sample-overriden-screen-class',
        ],
    ];

    /**
     * @internal
     */
    const SCREENS_CONFIG_RESULT = [
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

    /**
     * @var ChainCache|\PHPUnit_Framework_MockObject_MockObject
     */
    private $chainCache;

    /**
     * @var ThemeManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $themeManager;

    /**
     * @var ScreensProvider
     */
    private $screensProvider;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->chainCache = $this->createMock(ChainCache::class);
        $this->themeManager = $this->createMock(ThemeManager::class);
        $this->screensProvider = new ScreensProvider($this->themeManager, $this->chainCache);
    }

    public function testGetScreensWhenNoCache()
    {
        $this->chainCache
            ->expects(static::once())
            ->method('contains')
            ->with(self::SCREENS_CACHE_KEY)
            ->willReturn(false);

        $this->chainCache
            ->expects(static::once())
            ->method('save')
            ->with(self::SCREENS_CACHE_KEY, self::SCREENS_CONFIG_RESULT);

        $allThemes = [
            $this->createThemeMock(self::SCREENS_CONFIG_1),
            $this->createThemeMock(self::SCREENS_CONFIG_2),
        ];
        $this->themeManager
            ->expects(static::once())
            ->method('getAllThemes')
            ->willReturn($allThemes);

        $screens = $this->screensProvider->getScreens();

        static::assertEquals(self::SCREENS_CONFIG_RESULT, $screens);
    }

    public function testGetScreensFromCache()
    {
        $this->chainCache
            ->expects(static::once())
            ->method('contains')
            ->with(self::SCREENS_CACHE_KEY)
            ->willReturn(true);

        $this->chainCache
            ->expects(static::once())
            ->method('fetch')
            ->with(self::SCREENS_CACHE_KEY)
            ->willReturn(self::SCREENS_CONFIG_RESULT);

        $screens = $this->screensProvider->getScreens();

        static::assertEquals(self::SCREENS_CONFIG_RESULT, $screens);
    }

    /**
     * @dataProvider getScreenDataProvider
     *
     * @param string     $screenName
     * @param array|null $expectedScreen
     */
    public function testGetScreen($screenName, $expectedScreen)
    {
        $this->chainCache
            ->expects(static::atLeastOnce())
            ->method('contains')
            ->with(self::SCREENS_CACHE_KEY)
            ->willReturn(true);

        $this->chainCache
            ->expects(static::atLeastOnce())
            ->method('fetch')
            ->with(self::SCREENS_CACHE_KEY)
            ->willReturn(self::SCREENS_CONFIG_RESULT);

        $screen = $this->screensProvider->getScreen($screenName);

        static::assertSame($expectedScreen, $screen);
    }

    /**
     * @return array
     */
    public function getScreenDataProvider()
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
     *
     * @param string $screenName
     * @param bool   $expectedResult
     */
    public function testHasScreen($screenName, $expectedResult)
    {
        $this->chainCache
            ->expects(static::once())
            ->method('contains')
            ->with(self::SCREENS_CACHE_KEY)
            ->willReturn(true);

        $this->chainCache
            ->expects(static::once())
            ->method('fetch')
            ->with(self::SCREENS_CACHE_KEY)
            ->willReturn(self::SCREENS_CONFIG_RESULT);

        $result = $this->screensProvider->hasScreen($screenName);

        static::assertSame($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function hasScreenDataProvider()
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

    /**
     * @param array $screensConfig
     *
     * @return Theme|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createThemeMock(array $screensConfig)
    {
        $theme = $this->createMock(Theme::class);
        $theme
            ->expects(static::once())
            ->method('getConfig')
            ->willReturn(['screens' => $screensConfig]);

        return $theme;
    }
}
