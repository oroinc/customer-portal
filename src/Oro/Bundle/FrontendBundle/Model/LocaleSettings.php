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

    /**
     * {@inheritdoc}
     */
    public function addNameFormats(array $formats)
    {
        $this->inner->addNameFormats($formats);
    }

    /**
     * {@inheritdoc}
     */
    public function getNameFormats()
    {
        return $this->inner->getNameFormats();
    }

    /**
     * {@inheritdoc}
     */
    public function addAddressFormats(array $formats)
    {
        $this->inner->addAddressFormats($formats);
    }

    /**
     * {@inheritdoc}
     */
    public function getAddressFormats()
    {
        return $this->inner->getAddressFormats();
    }

    /**
     * {@inheritdoc}
     */
    public function addLocaleData(array $data)
    {
        $this->inner->addLocaleData($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getLocaleData()
    {
        return $this->inner->getLocaleData();
    }

    /**
     * {@inheritdoc}
     */
    public function isFormatAddressByAddressCountry()
    {
        return $this->inner->isFormatAddressByAddressCountry();
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function getLanguage()
    {
        if (!$this->frontendHelper->isFrontendRequest()) {
            return $this->inner->getLanguage();
        }

        $localization = $this->localizationProvider->getCurrentLocalization();

        return $localization ? $localization->getLanguageCode() : $this->inner->getLanguage();
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function getActualLanguage()
    {
        return $this->inner->getActualLanguage();
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function getCurrency()
    {
        return $this->inner->getCurrency();
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrencySymbolByCurrency(string $currencyCode = null, string $locale = null)
    {
        return $this->inner->getCurrencySymbolByCurrency($currencyCode ?: $this->getCurrency(), $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeZone()
    {
        return $this->inner->getTimeZone();
    }

    /**
     * {@inheritdoc}
     */
    public function getCalendar($locale = null, $language = null)
    {
        return $this->inner->getCalendar($locale ?: $this->getLocale(), $language ?: $this->getLanguage());
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function get($settingName)
    {
        return $this->inner->get($settingName);
    }

    /**
     * {@inheritdoc}
     */
    public function getLocalesByCodes(array $codes, $locale = 'en')
    {
        return $this->inner->getLocalesByCodes($codes, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstQuarterMonth()
    {
        return $this->inner->getFirstQuarterMonth();
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstQuarterDay()
    {
        return $this->inner->getFirstQuarterDay();
    }
}
