<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Model;

use Oro\Bundle\FrontendBundle\Model\LocaleSettings;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\FrontendLocalizationBundle\Manager\UserLocalizationManager;
use Oro\Bundle\LocaleBundle\DependencyInjection\Configuration as LocaleConfiguration;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Model\Calendar;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings as BaseLocaleSettings;
use Oro\Bundle\TranslationBundle\Entity\Language;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class LocaleSettingsTest extends \PHPUnit\Framework\TestCase
{
    /** @var BaseLocaleSettings|\PHPUnit\Framework\MockObject\MockObject */
    private $inner;

    /** @var FrontendHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $frontendHelper;

    /** @var UserLocalizationManager|\PHPUnit\Framework\MockObject\MockObject */
    private $localizationManager;

    /** @var LocaleSettings */
    protected $localeSettings;

    protected function setUp()
    {
        $this->inner = $this->createMock(BaseLocaleSettings::class);
        $this->frontendHelper = $this->createMock(FrontendHelper::class);
        $this->localizationManager = $this->createMock(UserLocalizationManager::class);

        $this->localeSettings = new LocaleSettings(
            $this->inner,
            $this->frontendHelper,
            $this->localizationManager
        );
    }

    public function testAddNameFormats()
    {
        $enFormat = ['en' => '%first_name% %middle_name% %last_name%'];
        $enFormatModified = ['en' => '%prefix% %%first_name% %middle_name% %last_name% %suffix%'];

        $this->inner->expects($this->once())
            ->method('getNameFormats')
            ->willReturn($enFormat);

        $this->assertEquals($enFormat, $this->localeSettings->getNameFormats());

        $this->inner->expects($this->once())
            ->method('addNameFormats')
            ->with($enFormatModified);

        $this->localeSettings->addNameFormats($enFormatModified);
    }

    public function testAddAddressFormats()
    {
        $usFormat = ['US' => [
            LocaleSettings::ADDRESS_FORMAT_KEY
            => '%name%\n%organization%\n%street%\n%CITY% %REGION% %COUNTRY% %postal_code%'
        ]];
        $usFormatModified = ['US' => [
            LocaleSettings::ADDRESS_FORMAT_KEY
            => '%name%\n%organization%\n%street%\n%CITY% %REGION_CODE% %COUNTRY% %postal_code%'
        ]];

        $this->inner->expects($this->once())
            ->method('getAddressFormats')
            ->willReturn($usFormat);

        $this->assertEquals($usFormat, $this->localeSettings->getAddressFormats());

        $this->inner->expects($this->once())
            ->method('addAddressFormats')
            ->with($usFormatModified);

        $this->localeSettings->addAddressFormats($usFormatModified);
    }

    public function testAddLocaleData()
    {
        $usData = ['US' => [LocaleSettings::DEFAULT_LOCALE_KEY => 'en_US']];
        $usDataModified = ['US' => [LocaleSettings::DEFAULT_LOCALE_KEY => 'en']];

        $this->inner->expects($this->once())
            ->method('getLocaleData')
            ->willReturn($usData);

        $this->assertEquals($usData, $this->localeSettings->getLocaleData());

        $this->inner->expects($this->once())
            ->method('addLocaleData')
            ->with($usDataModified);

        $this->localeSettings->addLocaleData($usDataModified);
    }

    public function testAddCurrencyData()
    {
        $usData = ['USD' => [LocaleSettings::CURRENCY_SYMBOL_KEY => '$']];
        $usDataModified = ['USD' => [LocaleSettings::CURRENCY_SYMBOL_KEY => 'AU$']];

        $this->inner->expects($this->once())
            ->method('getCurrencyData')
            ->willReturn($usData);

        $this->assertEquals($usData, $this->localeSettings->getCurrencyData());

        $this->inner->expects($this->once())
            ->method('addCurrencyData')
            ->with($usDataModified);

        $this->localeSettings->addCurrencyData($usDataModified);
    }

    public function testIsFormatAddressByAddressCountry()
    {
        $this->inner->expects($this->once())
            ->method('isFormatAddressByAddressCountry')
            ->willReturn(true);

        $this->assertTrue($this->localeSettings->isFormatAddressByAddressCountry());
    }

    public function testGetLocaleByCountry()
    {
        $countryCode = 'GB';
        $expectedLocale = 'en_GB';

        $this->frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->inner->expects($this->once())
            ->method('getLocaleByCountry')
            ->with($countryCode)
            ->willReturn($expectedLocale);

        $this->inner->expects($this->never())
            ->method('getLocaleData');

        $this->assertEquals($expectedLocale, $this->localeSettings->getLocaleByCountry($countryCode));
    }

    public function testGetLocaleByCountryWithLocaleData()
    {
        $countryCode = 'GB';
        $expectedLocale = 'en_GB';

        $this->frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->inner->expects($this->never())
            ->method('getLocaleByCountry');

        $this->inner->expects($this->once())
            ->method('getLocaleData')
            ->willReturn(['GB' => [LocaleSettings::DEFAULT_LOCALE_KEY => $expectedLocale]]);

        $this->assertEquals($expectedLocale, $this->localeSettings->getLocaleByCountry($countryCode));
    }

    public function testGetLocaleByCountryWithLocalization()
    {
        $countryCode = 'GB';
        $expectedLocale = 'en_GB';

        $this->frontendHelper->expects($this->atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->inner->expects($this->never())
            ->method('getLocaleByCountry');

        $this->inner->expects($this->once())
            ->method('getLocaleData')
            ->willReturn([]);

        $localization = new Localization();
        $localization->setFormattingCode($expectedLocale);

        $this->localizationManager->expects($this->once())
            ->method('getCurrentLocalization')
            ->willReturn($localization);

        $this->assertEquals($expectedLocale, $this->localeSettings->getLocaleByCountry($countryCode));
    }

    public function testGetLocaleByCountryWithoutLocalization()
    {
        $countryCode = 'GB';
        $expectedLocale = 'en_GB';

        $this->frontendHelper->expects($this->atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->inner->expects($this->never())
            ->method('getLocaleByCountry');

        $this->inner->expects($this->once())
            ->method('getLocaleData')
            ->willReturn([]);

        $this->inner->expects($this->once())
            ->method('getLocale')
            ->willReturn($expectedLocale);

        $this->localizationManager->expects($this->once())
            ->method('getCurrentLocalization')
            ->willReturn(null);

        $this->assertEquals($expectedLocale, $this->localeSettings->getLocaleByCountry($countryCode));
    }

    public function testGetLocale()
    {
        $this->frontendHelper->expects($this->atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->inner->expects($this->once())
            ->method('getLocale')
            ->willReturn('en_US');

        $this->assertEquals('en_US', $this->localeSettings->getLocale());
    }

    public function testGetLocaleWithLocalization()
    {
        $this->frontendHelper->expects($this->atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->inner->expects($this->never())
            ->method('getLocale');

        $localization = new Localization();
        $localization->setFormattingCode('de_DE');

        $this->localizationManager->expects($this->once())
            ->method('getCurrentLocalization')
            ->willReturn($localization);

        $this->assertEquals('de_DE', $this->localeSettings->getLocale());
    }

    public function testGetLocaleWithoutLocalization()
    {
        $this->frontendHelper->expects($this->atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->inner->expects($this->once())
            ->method('getLocale')
            ->willReturn('en_GB');

        $this->localizationManager->expects($this->once())
            ->method('getCurrentLocalization')
            ->willReturn(null);

        $this->assertEquals('en_GB', $this->localeSettings->getLocale());
    }

    public function testGetLanguage()
    {
        $this->frontendHelper->expects($this->atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->inner->expects($this->once())
            ->method('getLanguage')
            ->willReturn('en_US');

        $this->assertEquals('en_US', $this->localeSettings->getLanguage());
    }

    public function testGetLanguageWithLocalization()
    {
        $this->frontendHelper->expects($this->atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->inner->expects($this->never())
            ->method('getLanguage');

        $language = new Language();
        $language->setCode('de_DE');

        $localization = new Localization();
        $localization->setLanguage($language);

        $this->localizationManager->expects($this->once())
            ->method('getCurrentLocalization')
            ->willReturn($localization);

        $this->assertEquals('de_DE', $this->localeSettings->getLanguage());
    }

    public function testGetLanguageWithoutLocalization()
    {
        $this->frontendHelper->expects($this->atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->inner->expects($this->once())
            ->method('getLanguage')
            ->willReturn('en_GB');

        $this->localizationManager->expects($this->once())
            ->method('getCurrentLocalization')
            ->willReturn(null);

        $this->assertEquals('en_GB', $this->localeSettings->getLanguage());
    }

    public function testGetCountry()
    {
        $this->frontendHelper->expects($this->atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->inner->expects($this->once())
            ->method('getCountry')
            ->willReturn('US');

        $this->assertEquals('US', $this->localeSettings->getCountry());
    }

    public function testGetCountryWithoutConfig()
    {
        $this->frontendHelper->expects($this->atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->inner->expects($this->once())
            ->method('get')
            ->with('oro_locale.country')
            ->willReturn('CA');

        $this->assertEquals('CA', $this->localeSettings->getCountry());
        $this->assertEquals('CA', $this->localeSettings->getCountry());
    }

    public function testGetCountryWithConfig()
    {
        $this->frontendHelper->expects($this->atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->inner->expects($this->once())
            ->method('get')
            ->with('oro_locale.country')
            ->willReturn(null);

        $this->assertEquals('US', $this->localeSettings->getCountry());
        $this->assertEquals('US', $this->localeSettings->getCountry());
    }

    public function testGetCurrency()
    {
        $this->inner->expects($this->once())
            ->method('getCurrency')
            ->willReturn('USD');

        $this->assertEquals('USD', $this->localeSettings->getCurrency());
    }

    public function testGetCurrencySymbolByCurrency()
    {
        $this->inner->expects($this->once())
            ->method('getCurrency')
            ->willReturn('USD');

        $this->inner->expects($this->once())
            ->method('getCurrencySymbolByCurrency')
            ->with('USD')
            ->willReturn('$');

        $this->assertEquals('$', $this->localeSettings->getCurrencySymbolByCurrency());
    }

    public function testGetCurrencySymbolByCurrencyWithParameter()
    {
        $this->inner->expects($this->never())
            ->method('getCurrency');

        $this->inner->expects($this->once())
            ->method('getCurrencySymbolByCurrency')
            ->with('USD')
            ->willReturn('$');

        $this->assertEquals('$', $this->localeSettings->getCurrencySymbolByCurrency('USD'));
    }

    public function testGetTimeZone()
    {
        $this->inner->expects($this->once())
            ->method('getTimeZone')
            ->willReturn('UTC');

        $this->assertEquals('UTC', $this->localeSettings->getTimeZone());
    }

    public function testGetCalendarSymbolByCurrencyWithParameter()
    {
        $this->inner->expects($this->never())
            ->method('getLocale');

        $this->inner->expects($this->never())
            ->method('getLanguage');

        $this->inner->expects($this->once())
            ->method('getCalendar')
            ->with('en', 'en_US')
            ->willReturn(new Calendar());

        $this->assertEquals(new Calendar(), $this->localeSettings->getCalendar('en', 'en_US'));
    }

    public function testGetCalendar()
    {
        $this->inner->expects($this->once())
            ->method('getLocale')
            ->willReturn('en');

        $this->inner->expects($this->once())
            ->method('getLanguage')
            ->willReturn('en_US');

        $this->inner->expects($this->once())
            ->method('getCalendar')
            ->with('en', 'en_US')
            ->willReturn(new Calendar());

        $this->assertEquals(new Calendar(), $this->localeSettings->getCalendar());
    }

    public function testGetLocaleWithRegion()
    {
        $this->frontendHelper->expects($this->atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->inner->expects($this->once())
            ->method('getLocaleWithRegion')
            ->willReturn('en_US');

        $this->assertEquals('en_US', $this->localeSettings->getLocaleWithRegion());
    }

    public function testGetLocaleWithRegionFromLocale()
    {
        $this->frontendHelper->expects($this->atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->inner->expects($this->once())
            ->method('getLocale')
            ->willReturn('en_CA');

        $this->assertEquals('en_CA', $this->localeSettings->getLocaleWithRegion());
    }

    public function testGetLocaleWithRegionFromCountry()
    {
        $this->frontendHelper->expects($this->atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->inner->expects($this->atLeastOnce())
            ->method('getLocale')
            ->willReturn('US');

        $this->inner->expects($this->atLeastOnce())
            ->method('get')
            ->with('oro_locale.country')
            ->willReturn('US');

        $this->inner->expects($this->once())
            ->method('getLocaleData')
            ->willReturn(['US' => [LocaleSettings::DEFAULT_LOCALE_KEY => 'en_US']]);

        $this->assertEquals('en_US', $this->localeSettings->getLocaleWithRegion());
    }

    public function testGet()
    {
        $this->inner->expects($this->once())
            ->method('get')
            ->with('param')
            ->willReturn('data');

        $this->assertEquals('data', $this->localeSettings->get('param'));
    }

    public function testGetLocalesByCodes()
    {
        $this->inner->expects($this->once())
            ->method('getLocalesByCodes')
            ->with(['codes'], 'de')
            ->willReturn(['locales']);

        $this->assertEquals(['locales'], $this->localeSettings->getLocalesByCodes(['codes'], 'de'));
    }

    public function testGetFirstQuarterMonth()
    {
        $this->inner->expects($this->once())
            ->method('getFirstQuarterMonth')
            ->willReturn(1);

        $this->assertEquals(1, $this->localeSettings->getFirstQuarterMonth());
    }

    public function testGetFirstQuarterDay()
    {
        $this->inner->expects($this->once())
            ->method('getFirstQuarterDay')
            ->willReturn(2);

        $this->assertEquals(2, $this->localeSettings->getFirstQuarterDay());
    }

    /**
     * @dataProvider getValidLocaleDataProvider
     *
     * @param string $locale
     * @param string $expectedLocale
     */
    public function testGetValidLocale($locale, $expectedLocale)
    {
        $this->assertEquals($expectedLocale, LocaleSettings::getValidLocale($locale));
    }

    /**
     * @return array
     */
    public function getValidLocaleDataProvider()
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
     *
     * @param string $locale
     * @param string $expectedCountry
     */
    public function testGetCountryByLocale($locale, $expectedCountry)
    {
        $this->assertEquals($expectedCountry, LocaleSettings::getCountryByLocale($locale));
    }

    /**
     * @return array
     */
    public function getCountryByLocaleDataProvider()
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
     *
     * @param string $locale
     * @param string $expectedCurrency
     */
    public function testGetCountryByLocal($locale, $expectedCurrency)
    {
        $currency = LocaleSettings::getCurrencyByLocale($locale);

        $this->assertEquals($expectedCurrency, $currency);
    }

    /**
     * The USD is default currency
     *
     * @return array
     */
    public function localeProvider()
    {
        return [
            [
                'en',
                'USD'
            ],
            [
                'en_CA',
                $this->getCurrencyBuLocale('en_CA')
            ],
            [
                'it',
                'USD'
            ],
            [
                'it_IT',
                $this->getCurrencyBuLocale('it_IT')
            ],
            [
                'ua',
                'USD'
            ],
            [
                'ru_UA',
                $this->getCurrencyBuLocale('ru_UA')
            ]
        ];
    }

    /**
     * @param string $locale
     * @return bool|string
     */
    protected function getCurrencyBuLocale($locale)
    {
        $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);

        return $formatter->getTextAttribute(\NumberFormatter::CURRENCY_CODE);
    }
}
