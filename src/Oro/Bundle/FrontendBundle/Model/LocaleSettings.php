<?php

namespace Oro\Bundle\FrontendBundle\Model;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\FrontendLocalizationBundle\Manager\UserLocalizationManagerInterface;
use Oro\Bundle\LayoutBundle\Layout\LayoutContextHolder;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings as BaseLocaleSettings;
use Oro\Component\Layout\Extension\Theme\Model\ThemeManager;

/**
 * Provides locale settings for store front.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class LocaleSettings extends BaseLocaleSettings
{
    /**
     * @var BaseLocaleSettings
     */
    protected $inner;

    /**
     * @var FrontendHelper
     */
    protected $frontendHelper;

    /**
     * @var UserLocalizationManagerInterface
     */
    protected $localizationManager;

    /**
     * @var LayoutContextHolder
     */
    protected $layoutContextHolder;

    /**
     * @var ThemeManager
     */
    private $themeManager;

    public function __construct(
        BaseLocaleSettings $inner,
        FrontendHelper $frontendHelper,
        UserLocalizationManagerInterface $localizationManager
    ) {
        $this->inner = $inner;
        $this->frontendHelper = $frontendHelper;
        $this->localizationManager = $localizationManager;
    }

    public function setLayoutContextHolder(LayoutContextHolder $layoutContextHolder): void
    {
        $this->layoutContextHolder = $layoutContextHolder;
    }

    public function setThemeManager(ThemeManager $themeManager): void
    {
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
                $localization = $this->localizationManager->getCurrentLocalization();

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

        $localization = $this->localizationManager->getCurrentLocalization();

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
        if (!$this->layoutContextHolder || !$this->themeManager) {
            return false;
        }

        $context = $this->layoutContextHolder->getContext();
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

        $localization = $this->localizationManager->getCurrentLocalization();

        return $localization ? $localization->isRtlMode() : false;
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
