<?php

namespace Oro\Bundle\FrontendBundle\Model;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings as BaseLocaleSettings;
use Oro\Bundle\LocaleBundle\Provider\LocalizationProviderInterface;
use Oro\Component\Layout\Extension\Theme\Model\ThemeManager;
use Oro\Component\Layout\LayoutContextStack;

/**
 * Provides locale settings for store front.
 */
class LocaleSettings extends BaseLocaleSettings
{
    protected BaseLocaleSettings $inner;

    protected FrontendHelper $frontendHelper;

    protected LocalizationProviderInterface $localizationProvider;

    protected LayoutContextStack $layoutContextStack;

    private ThemeManager $themeManager;

    public function __construct(
        BaseLocaleSettings $inner,
        FrontendHelper $frontendHelper,
        LocalizationProviderInterface $localizationProvider,
        LayoutContextStack $layoutContextStack,
        ThemeManager $themeManager
    ) {
        $this->inner = $inner;
        $this->frontendHelper = $frontendHelper;
        $this->localizationProvider = $localizationProvider;
        $this->layoutContextStack = $layoutContextStack;
        $this->themeManager = $themeManager;
    }

    #[\Override]
    public function addNameFormats(array $formats)
    {
        $this->inner->addNameFormats($formats);
    }

    #[\Override]
    public function getNameFormats()
    {
        return $this->inner->getNameFormats();
    }

    #[\Override]
    public function addAddressFormats(array $formats)
    {
        $this->inner->addAddressFormats($formats);
    }

    #[\Override]
    public function getAddressFormats()
    {
        return $this->inner->getAddressFormats();
    }

    #[\Override]
    public function addLocaleData(array $data)
    {
        $this->inner->addLocaleData($data);
    }

    #[\Override]
    public function getLocaleData()
    {
        return $this->inner->getLocaleData();
    }

    #[\Override]
    public function isFormatAddressByAddressCountry()
    {
        return $this->inner->isFormatAddressByAddressCountry();
    }

    #[\Override]
    public function getLocaleByCountry($country)
    {
        if (!$this->frontendHelper->isFrontendRequest()) {
            return $this->inner->getLocaleByCountry($country);
        }

        $localeData = $this->getLocaleData();
        if (isset($localeData[$country][self::DEFAULT_LOCALE_KEY])) {
            return $localeData[$country][self::DEFAULT_LOCALE_KEY];
        }

        return $this->getLocale();
    }

    #[\Override]
    public function getLocale()
    {
        if ($this->locale === null) {
            if (!$this->frontendHelper->isFrontendRequest()) {
                $this->locale = $this->inner->getLocale();
            } else {
                $localization = $this->localizationProvider->getCurrentLocalization();

                $this->locale = $localization ? $localization->getFormattingCode() : $this->inner->getLocale();
            }
        }

        return $this->locale;
    }

    #[\Override]
    public function getLanguage()
    {
        if (!$this->frontendHelper->isFrontendRequest()) {
            return $this->inner->getLanguage();
        }

        $localization = $this->localizationProvider->getCurrentLocalization();

        return $localization ? $localization->getLanguageCode() : $this->inner->getLanguage();
    }

    #[\Override]
    public function isRtlMode(): bool
    {
        if ($this->rtlMode === null) {
            $this->rtlMode = $this->frontendHelper->isFrontendRequest()
                ? $this->isRtlModeForLayoutRequest()
                : $this->inner->isRtlMode();
        }

        return $this->rtlMode;
    }

    private function isRtlModeForLayoutRequest(): bool
    {
        $context = $this->layoutContextStack->getCurrentContext();
        if (!$context || !$context->offsetExists('theme')) {
            return false;
        }

        $themeName = $context->offsetGet('theme');
        if (!$themeName || !$this->themeManager->hasTheme($themeName)) {
            return false;
        }

        $theme = $this->themeManager->getTheme($themeName);
        if (!$theme->isRtlSupport()) {
            return false;
        }

        $localization = $this->localizationProvider->getCurrentLocalization();

        return $localization && $localization->isRtlMode();
    }

    #[\Override]
    public function getActualLanguage()
    {
        return $this->inner->getActualLanguage();
    }

    #[\Override]
    public function getCountry()
    {
        if (!$this->frontendHelper->isFrontendRequest()) {
            return $this->inner->getCountry();
        }

        if (null === $this->country) {
            $this->country = $this->inner->get('oro_locale.country');
            if (!$this->country) {
                $this->country = self::getCountryByLocale($this->getLocale());
            }
        }
        return $this->country;
    }

    #[\Override]
    public function getCurrency()
    {
        return $this->inner->getCurrency();
    }

    #[\Override]
    public function getCurrencySymbolByCurrency(?string $currencyCode = null, ?string $locale = null)
    {
        return $this->inner->getCurrencySymbolByCurrency($currencyCode ?: $this->getCurrency(), $locale);
    }

    #[\Override]
    public function getTimeZone()
    {
        return $this->inner->getTimeZone();
    }

    #[\Override]
    public function getCalendar($locale = null, $language = null)
    {
        return $this->inner->getCalendar($locale ?: $this->getLocale(), $language ?: $this->getLanguage());
    }

    #[\Override]
    public function getLocaleWithRegion()
    {
        if (!$this->frontendHelper->isFrontendRequest()) {
            return $this->inner->getLocaleWithRegion();
        }

        $locale = $this->getLocale();
        if (strlen($locale) > 2) {
            return $locale;
        }

        return $this->getLocaleByCountry($this->getCountry());
    }

    #[\Override]
    public function get($settingName)
    {
        return $this->inner->get($settingName);
    }

    #[\Override]
    public function getLocalesByCodes(array $codes, $locale = 'en')
    {
        return $this->inner->getLocalesByCodes($codes, $locale);
    }

    #[\Override]
    public function getFirstQuarterMonth()
    {
        return $this->inner->getFirstQuarterMonth();
    }

    #[\Override]
    public function getFirstQuarterDay()
    {
        return $this->inner->getFirstQuarterDay();
    }
}
