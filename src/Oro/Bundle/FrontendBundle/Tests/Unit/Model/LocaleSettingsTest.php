<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Model;

use Oro\Bundle\FrontendBundle\Model\LocaleSettings;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\LocaleBundle\DependencyInjection\Configuration as LocaleConfiguration;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Model\Calendar;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings as BaseLocaleSettings;
use Oro\Bundle\LocaleBundle\Provider\LocalizationProviderInterface;
use Oro\Bundle\ThemeBundle\Model\Theme;
use Oro\Bundle\TranslationBundle\Entity\Language;
use Oro\Component\Layout\Extension\Theme\Model\ThemeManager;
use Oro\Component\Layout\LayoutContextStack;
use Oro\Component\Layout\Tests\Unit\Stubs\LayoutContextStub;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class LocaleSettingsTest extends TestCase
{
    private BaseLocaleSettings|\PHPUnit\Framework\MockObject\MockObject $inner;

    private FrontendHelper|\PHPUnit\Framework\MockObject\MockObject $frontendHelper;

    private LocalizationProviderInterface|\PHPUnit\Framework\MockObject\MockObject $localizationProvider;

    private LayoutContextStack|\PHPUnit\Framework\MockObject\MockObject $layoutContextStack;

    private ThemeManager|\PHPUnit\Framework\MockObject\MockObject $themeManager;

    private LocaleSettings $localeSettings;

    protected function setUp(): void
    {
        $this->inner = $this->createMock(BaseLocaleSettings::class);
        $this->frontendHelper = $this->createMock(FrontendHelper::class);
        $this->localizationProvider = $this->createMock(LocalizationProviderInterface::class);
        $this->layoutContextStack = $this->createMock(LayoutContextStack::class);
        $this->themeManager = $this->createMock(ThemeManager::class);

        $this->localeSettings = new LocaleSettings(
            $this->inner,
            $this->frontendHelper,
            $this->localizationProvider,
            $this->layoutContextStack,
            $this->themeManager
        );
    }

    public function testAddNameFormats(): void
    {
        $enFormat = ['en' => '%first_name% %middle_name% %last_name%'];
        $enFormatModified = ['en' => '%prefix% %%first_name% %middle_name% %last_name% %suffix%'];

        $this->inner->expects(self::once())
            ->method('getNameFormats')
            ->willReturn($enFormat);

        self::assertEquals($enFormat, $this->localeSettings->getNameFormats());

        $this->inner->expects(self::once())
            ->method('addNameFormats')
            ->with($enFormatModified);

        $this->localeSettings->addNameFormats($enFormatModified);
    }

    public function testAddAddressFormats(): void
    {
        $usFormat = [
            'US' => [
                BaseLocaleSettings::ADDRESS_FORMAT_KEY
                => '%name%\n%organization%\n%street%\n%CITY% %REGION% %COUNTRY% %postal_code%',
            ],
        ];
        $usFormatModified = [
            'US' => [
                BaseLocaleSettings::ADDRESS_FORMAT_KEY
                => '%name%\n%organization%\n%street%\n%CITY% %REGION_CODE% %COUNTRY% %postal_code%',
            ],
        ];

        $this->inner->expects(self::once())
            ->method('getAddressFormats')
            ->willReturn($usFormat);

        self::assertEquals($usFormat, $this->localeSettings->getAddressFormats());

        $this->inner->expects(self::once())
            ->method('addAddressFormats')
            ->with($usFormatModified);

        $this->localeSettings->addAddressFormats($usFormatModified);
    }

    public function testAddLocaleData(): void
    {
        $usData = ['US' => [BaseLocaleSettings::DEFAULT_LOCALE_KEY => 'en_US']];
        $usDataModified = ['US' => [BaseLocaleSettings::DEFAULT_LOCALE_KEY => 'en']];

        $this->inner->expects(self::once())
            ->method('getLocaleData')
            ->willReturn($usData);

        self::assertEquals($usData, $this->localeSettings->getLocaleData());

        $this->inner->expects(self::once())
            ->method('addLocaleData')
            ->with($usDataModified);

        $this->localeSettings->addLocaleData($usDataModified);
    }

    public function testIsFormatAddressByAddressCountry(): void
    {
        $this->inner->expects(self::once())
            ->method('isFormatAddressByAddressCountry')
            ->willReturn(true);

        self::assertTrue($this->localeSettings->isFormatAddressByAddressCountry());
    }

    public function testGetLocaleByCountry(): void
    {
        $countryCode = 'GB';
        $expectedLocale = 'en_GB';

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->inner->expects(self::once())
            ->method('getLocaleByCountry')
            ->with($countryCode)
            ->willReturn($expectedLocale);

        $this->inner->expects(self::never())
            ->method('getLocaleData');

        self::assertEquals($expectedLocale, $this->localeSettings->getLocaleByCountry($countryCode));
    }

    public function testGetLocaleByCountryWithLocaleData(): void
    {
        $countryCode = 'GB';
        $expectedLocale = 'en_GB';

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->inner->expects(self::never())
            ->method('getLocaleByCountry');

        $this->inner->expects(self::once())
            ->method('getLocaleData')
            ->willReturn(['GB' => [BaseLocaleSettings::DEFAULT_LOCALE_KEY => $expectedLocale]]);

        self::assertEquals($expectedLocale, $this->localeSettings->getLocaleByCountry($countryCode));
    }

    public function testGetLocaleByCountryWithLocalization(): void
    {
        $countryCode = 'GB';
        $expectedLocale = 'en_GB';

        $this->frontendHelper->expects(self::atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->inner->expects(self::never())
            ->method('getLocaleByCountry');

        $this->inner->expects(self::once())
            ->method('getLocaleData')
            ->willReturn([]);

        $localization = new Localization();
        $localization->setFormattingCode($expectedLocale);

        $this->localizationProvider->expects(self::once())
            ->method('getCurrentLocalization')
            ->willReturn($localization);

        self::assertEquals($expectedLocale, $this->localeSettings->getLocaleByCountry($countryCode));
    }

    public function testGetLocaleByCountryWithoutLocalization(): void
    {
        $countryCode = 'GB';
        $expectedLocale = 'en_GB';

        $this->frontendHelper->expects(self::atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->inner->expects(self::never())
            ->method('getLocaleByCountry');

        $this->inner->expects(self::once())
            ->method('getLocaleData')
            ->willReturn([]);

        $this->inner->expects(self::once())
            ->method('getLocale')
            ->willReturn($expectedLocale);

        $this->localizationProvider->expects(self::once())
            ->method('getCurrentLocalization')
            ->willReturn(null);

        self::assertEquals($expectedLocale, $this->localeSettings->getLocaleByCountry($countryCode));
    }

    public function testGetLocale(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->inner->expects(self::once())
            ->method('getLocale')
            ->willReturn('en_US');

        self::assertEquals('en_US', $this->localeSettings->getLocale());

        // check local cache
        self::assertEquals('en_US', $this->localeSettings->getLocale());
    }

    public function testGetLocaleWithLocalization(): void
    {
        $this->frontendHelper->expects(self::atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->inner->expects(self::never())
            ->method('getLocale');

        $localization = new Localization();
        $localization->setFormattingCode('de_DE');

        $this->localizationProvider->expects(self::once())
            ->method('getCurrentLocalization')
            ->willReturn($localization);

        self::assertEquals('de_DE', $this->localeSettings->getLocale());

        // check local cache
        self::assertEquals('de_DE', $this->localeSettings->getLocale());
    }

    public function testGetLocaleWithoutLocalization(): void
    {
        $this->frontendHelper->expects(self::atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->inner->expects(self::once())
            ->method('getLocale')
            ->willReturn('en_GB');

        $this->localizationProvider->expects(self::once())
            ->method('getCurrentLocalization')
            ->willReturn(null);

        self::assertEquals('en_GB', $this->localeSettings->getLocale());
    }

    public function testGetLanguage(): void
    {
        $this->frontendHelper->expects(self::atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->inner->expects(self::once())
            ->method('getLanguage')
            ->willReturn('en_US');

        self::assertEquals('en_US', $this->localeSettings->getLanguage());
    }

    public function testGetActualLanguage(): void
    {
        $this->inner->expects(self::once())
            ->method('getActualLanguage')
            ->willReturn('en_US');

        self::assertEquals('en_US', $this->localeSettings->getActualLanguage());
    }

    public function testGetLanguageWithLocalization(): void
    {
        $this->frontendHelper->expects(self::atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->inner->expects(self::never())
            ->method('getLanguage');

        $language = new Language();
        $language->setCode('de_DE');

        $localization = new Localization();
        $localization->setLanguage($language);

        $this->localizationProvider->expects(self::once())
            ->method('getCurrentLocalization')
            ->willReturn($localization);

        self::assertEquals('de_DE', $this->localeSettings->getLanguage());
    }

    public function testGetLanguageWithoutLocalization(): void
    {
        $this->frontendHelper->expects(self::atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->inner->expects(self::once())
            ->method('getLanguage')
            ->willReturn('en_GB');

        $this->localizationProvider->expects(self::once())
            ->method('getCurrentLocalization')
            ->willReturn(null);

        self::assertEquals('en_GB', $this->localeSettings->getLanguage());
    }

    public function testIsRtlModeEnabledWhenBackendRequest(): void
    {
        $this->frontendHelper->expects(self::atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->layoutContextStack->expects(self::never())
            ->method('getCurrentContext');

        $this->themeManager->expects(self::never())
            ->method('hasTheme');

        $this->localizationProvider->expects(self::never())
            ->method('getCurrentLocalization');

        $this->inner->expects(self::once())
            ->method('isRtlMode')
            ->willReturn(true);

        self::assertTrue($this->localeSettings->isRtlMode());
    }

    public function testIsRtlModeEnabledWhenNoThemeInContext(): void
    {
        $this->frontendHelper->expects(self::atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $context = new LayoutContextStub([], true);

        $this->layoutContextStack->expects(self::any())
            ->method('getCurrentContext')
            ->willReturn($context);

        $this->themeManager->expects(self::never())
            ->method('hasTheme');

        $localization = new Localization();
        $localization->setRtlMode(true);

        $this->localizationProvider->expects(self::any())
            ->method('getCurrentLocalization')
            ->willReturn($localization);

        self::assertFalse($this->localeSettings->isRtlMode());
    }

    public function testIsRtlModeEnabledWhenNoActiveTheme(): void
    {
        $this->frontendHelper->expects(self::atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $context = new LayoutContextStub(['theme' => 'test'], true);

        $this->layoutContextStack->expects(self::any())
            ->method('getCurrentContext')
            ->willReturn($context);

        $this->themeManager->expects(self::any())
            ->method('hasTheme')
            ->with('test')
            ->willReturn(false);

        $localization = new Localization();
        $localization->setRtlMode(true);

        $this->localizationProvider->expects(self::any())
            ->method('getCurrentLocalization')
            ->willReturn($localization);

        self::assertFalse($this->localeSettings->isRtlMode());
    }

    public function testIsRtlModeEnabledNoLocalization(): void
    {
        $this->frontendHelper->expects(self::atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $theme = new Theme('test');
        $theme->setRtlSupport(true);

        $context = new LayoutContextStub(['theme' => $theme->getName()], true);

        $this->layoutContextStack->expects(self::any())
            ->method('getCurrentContext')
            ->willReturn($context);

        $this->themeManager->expects(self::any())
            ->method('hasTheme')
            ->with($theme->getName())
            ->willReturn(true);

        $this->themeManager->expects(self::any())
            ->method('getTheme')
            ->with($theme->getName())
            ->willReturn($theme);

        $this->layoutContextStack->expects(self::any())
            ->method('getCurrentContext')
            ->willReturn($context);

        $this->localizationProvider->expects(self::any())
            ->method('getCurrentLocalization')
            ->willReturn(null);

        self::assertFalse($this->localeSettings->isRtlMode());
    }

    public function testIsRtlModeEnabledWhenThemeWithoutRtl(): void
    {
        $this->frontendHelper->expects(self::atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $theme = new Theme('test');
        $theme->setRtlSupport(false);

        $context = new LayoutContextStub(['theme' => $theme->getName()], true);

        $this->layoutContextStack->expects(self::any())
            ->method('getCurrentContext')
            ->willReturn($context);

        $this->themeManager->expects(self::any())
            ->method('hasTheme')
            ->with($theme->getName())
            ->willReturn(true);

        $this->themeManager->expects(self::any())
            ->method('getTheme')
            ->with($theme->getName())
            ->willReturn($theme);

        $this->layoutContextStack->expects(self::any())
            ->method('getCurrentContext')
            ->willReturn($context);

        $localization = new Localization();
        $localization->setRtlMode(true);

        $this->localizationProvider->expects(self::any())
            ->method('getCurrentLocalization')
            ->willReturn($localization);

        self::assertFalse($this->localeSettings->isRtlMode());
    }

    public function testIsRtlModeEnabledWhenLocalizationWithoutRtl(): void
    {
        $this->frontendHelper->expects(self::atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $theme = new Theme('test');
        $theme->setRtlSupport(true);

        $context = new LayoutContextStub(['theme' => $theme->getName()], true);

        $this->layoutContextStack->expects(self::any())
            ->method('getCurrentContext')
            ->willReturn($context);

        $this->themeManager->expects(self::any())
            ->method('hasTheme')
            ->with($theme->getName())
            ->willReturn(true);

        $this->themeManager->expects(self::any())
            ->method('getTheme')
            ->with($theme->getName())
            ->willReturn($theme);

        $this->layoutContextStack->expects(self::any())
            ->method('getCurrentContext')
            ->willReturn($context);

        $localization = new Localization();
        $localization->setRtlMode(false);

        $this->localizationProvider->expects(self::any())
            ->method('getCurrentLocalization')
            ->willReturn($localization);

        self::assertFalse($this->localeSettings->isRtlMode());
    }

    public function testIsRtlModeEnabled(): void
    {
        $this->frontendHelper->expects(self::atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $theme = new Theme('test');
        $theme->setRtlSupport(true);

        $context = new LayoutContextStub(['theme' => $theme->getName()], true);

        $this->layoutContextStack->expects(self::any())
            ->method('getCurrentContext')
            ->willReturn($context);

        $this->themeManager->expects(self::any())
            ->method('hasTheme')
            ->with($theme->getName())
            ->willReturn(true);

        $this->themeManager->expects(self::any())
            ->method('getTheme')
            ->with($theme->getName())
            ->willReturn($theme);

        $this->layoutContextStack->expects(self::any())
            ->method('getCurrentContext')
            ->willReturn($context);

        $localization = new Localization();
        $localization->setRtlMode(true);

        $this->localizationProvider->expects(self::any())
            ->method('getCurrentLocalization')
            ->willReturn($localization);

        self::assertTrue($this->localeSettings->isRtlMode());
    }

    public function testGetCountry(): void
    {
        $this->frontendHelper->expects(self::atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->inner->expects(self::once())
            ->method('getCountry')
            ->willReturn('US');

        self::assertEquals('US', $this->localeSettings->getCountry());
    }

    public function testGetCountryWithoutConfig(): void
    {
        $this->frontendHelper->expects(self::atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->inner->expects(self::once())
            ->method('get')
            ->with('oro_locale.country')
            ->willReturn('CA');

        self::assertEquals('CA', $this->localeSettings->getCountry());
        self::assertEquals('CA', $this->localeSettings->getCountry());
    }

    public function testGetCountryWithConfig(): void
    {
        $this->frontendHelper->expects(self::atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->inner->expects(self::once())
            ->method('get')
            ->with('oro_locale.country')
            ->willReturn(null);

        self::assertEquals('US', $this->localeSettings->getCountry());
        self::assertEquals('US', $this->localeSettings->getCountry());
    }

    public function testGetCurrency(): void
    {
        $this->inner->expects(self::once())
            ->method('getCurrency')
            ->willReturn('USD');

        self::assertEquals('USD', $this->localeSettings->getCurrency());
    }

    public function testGetCurrencySymbolByCurrency(): void
    {
        $this->inner->expects(self::once())
            ->method('getCurrency')
            ->willReturn('USD');

        $this->inner->expects(self::once())
            ->method('getCurrencySymbolByCurrency')
            ->with('USD')
            ->willReturn('$');

        self::assertEquals('$', $this->localeSettings->getCurrencySymbolByCurrency());
    }

    public function testGetCurrencySymbolByCurrencyWithParameter(): void
    {
        $this->inner->expects(self::never())
            ->method('getCurrency');

        $this->inner->expects(self::once())
            ->method('getCurrencySymbolByCurrency')
            ->with('USD')
            ->willReturn('$');

        self::assertEquals('$', $this->localeSettings->getCurrencySymbolByCurrency('USD'));
    }

    public function testGetTimeZone(): void
    {
        $this->inner->expects(self::once())
            ->method('getTimeZone')
            ->willReturn('UTC');

        self::assertEquals('UTC', $this->localeSettings->getTimeZone());
    }

    public function testGetCalendarSymbolByCurrencyWithParameter(): void
    {
        $this->inner->expects(self::never())
            ->method('getLocale');

        $this->inner->expects(self::never())
            ->method('getLanguage');

        $this->inner->expects(self::once())
            ->method('getCalendar')
            ->with('en', 'en_US')
            ->willReturn(new Calendar());

        self::assertEquals(new Calendar(), $this->localeSettings->getCalendar('en', 'en_US'));
    }

    public function testGetCalendar(): void
    {
        $this->inner->expects(self::once())
            ->method('getLocale')
            ->willReturn('en');

        $this->inner->expects(self::once())
            ->method('getLanguage')
            ->willReturn('en_US');

        $this->inner->expects(self::once())
            ->method('getCalendar')
            ->with('en', 'en_US')
            ->willReturn(new Calendar());

        self::assertEquals(new Calendar(), $this->localeSettings->getCalendar());
    }

    public function testGetLocaleWithRegion(): void
    {
        $this->frontendHelper->expects(self::atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->inner->expects(self::once())
            ->method('getLocaleWithRegion')
            ->willReturn('en_US');

        self::assertEquals('en_US', $this->localeSettings->getLocaleWithRegion());
    }

    public function testGetLocaleWithRegionFromLocale(): void
    {
        $this->frontendHelper->expects(self::atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->inner->expects(self::once())
            ->method('getLocale')
            ->willReturn('en_CA');

        self::assertEquals('en_CA', $this->localeSettings->getLocaleWithRegion());
    }

    public function testGetLocaleWithRegionFromCountry(): void
    {
        $this->frontendHelper->expects(self::atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->inner->expects(self::atLeastOnce())
            ->method('getLocale')
            ->willReturn('US');

        $this->inner->expects(self::atLeastOnce())
            ->method('get')
            ->with('oro_locale.country')
            ->willReturn('US');

        $this->inner->expects(self::once())
            ->method('getLocaleData')
            ->willReturn(['US' => [BaseLocaleSettings::DEFAULT_LOCALE_KEY => 'en_US']]);

        self::assertEquals('en_US', $this->localeSettings->getLocaleWithRegion());
    }

    public function testGet(): void
    {
        $this->inner->expects(self::once())
            ->method('get')
            ->with('param')
            ->willReturn('data');

        self::assertEquals('data', $this->localeSettings->get('param'));
    }

    public function testGetLocalesByCodes(): void
    {
        $this->inner->expects(self::once())
            ->method('getLocalesByCodes')
            ->with(['codes'], 'de')
            ->willReturn(['locales']);

        self::assertEquals(['locales'], $this->localeSettings->getLocalesByCodes(['codes'], 'de'));
    }

    public function testGetFirstQuarterMonth(): void
    {
        $this->inner->expects(self::once())
            ->method('getFirstQuarterMonth')
            ->willReturn(1);

        self::assertEquals(1, $this->localeSettings->getFirstQuarterMonth());
    }

    public function testGetFirstQuarterDay(): void
    {
        $this->inner->expects(self::once())
            ->method('getFirstQuarterDay')
            ->willReturn(2);

        self::assertEquals(2, $this->localeSettings->getFirstQuarterDay());
    }

    /**
     * @dataProvider getValidLocaleDataProvider
     */
    public function testGetValidLocale(?string $locale, string $expectedLocale): void
    {
        self::assertEquals($expectedLocale, LocaleSettings::getValidLocale($locale));
    }

    public function getValidLocaleDataProvider(): array
    {
        return [
            ['ru_RU', 'ru_RU'],
            ['en', LocaleConfiguration::DEFAULT_LOCALE],
            [null, LocaleConfiguration::DEFAULT_LOCALE],
            ['ru', 'ru'],
            ['en_Hans_CN_nedis_rozaj_x_prv1_prv2', 'en_US'],
            ['en_Hans_CA_nedis_rozaj_x_prv1_prv2', 'en_CA'],
            ['unknown', 'en_US'],
        ];
    }

    /**
     * @dataProvider getCountryByLocaleDataProvider
     */
    public function testGetCountryByLocale(string $locale, string $expectedCountry): void
    {
        self::assertEquals($expectedCountry, LocaleSettings::getCountryByLocale($locale));
    }

    public function getCountryByLocaleDataProvider(): array
    {
        return [
            ['EN', LocaleConfiguration::DEFAULT_COUNTRY],
            ['RU', LocaleConfiguration::DEFAULT_COUNTRY],
            ['en_US', 'US'],
            ['en_XX', LocaleConfiguration::DEFAULT_COUNTRY],
        ];
    }

    /**
     * @dataProvider localeProvider
     */
    public function testGetCountryByLocal(string $locale, string $expectedCurrency): void
    {
        $currency = LocaleSettings::getCurrencyByLocale($locale);

        self::assertEquals($expectedCurrency, $currency);
    }

    /**
     * The USD is default currency
     */
    public function localeProvider(): array
    {
        return [
            ['en', 'USD'],
            ['en_CA', $this->getCurrencyByLocale('en_CA')],
            ['it', 'USD'],
            ['it_IT', $this->getCurrencyByLocale('it_IT')],
            ['ua', 'USD'],
            ['ru_UA', $this->getCurrencyByLocale('ru_UA')],
        ];
    }

    private function getCurrencyByLocale(string $locale): string
    {
        return (new \NumberFormatter($locale, \NumberFormatter::CURRENCY))->getTextAttribute(
            \NumberFormatter::CURRENCY_CODE
        );
    }
}
