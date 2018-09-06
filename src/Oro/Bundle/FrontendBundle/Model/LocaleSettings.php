<?php

namespace Oro\Bundle\FrontendBundle\Model;

use Oro\Bundle\CurrencyBundle\Model\LocaleSettings as CurrencyLocaleSettings;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\FrontendLocalizationBundle\Manager\UserLocalizationManager;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings as BaseLocaleSettings;

class LocaleSettings extends CurrencyLocaleSettings
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
     * @var UserLocalizationManager
     */
    protected $localizationManager;

    /**
     * @param BaseLocaleSettings $inner
     * @param FrontendHelper $frontendHelper
     * @param UserLocalizationManager $localizationManager
     */
    public function __construct(
        BaseLocaleSettings $inner,
        FrontendHelper $frontendHelper,
        UserLocalizationManager $localizationManager
    ) {
        $this->inner = $inner;
        $this->frontendHelper = $frontendHelper;
        $this->localizationManager = $localizationManager;
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
    public function addCurrencyData(array $data)
    {
        $this->inner->addCurrencyData($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrencyData()
    {
        return $this->inner->getCurrencyData();
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
        if (!$this->frontendHelper->isFrontendRequest()) {
            return $this->inner->getLocale();
        }

        $localization = $this->localizationManager->getCurrentLocalization();

        return $localization ? $localization->getFormattingCode() : $this->inner->getLocale();
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
    public function getCurrencySymbolByCurrency($currencyCode = null)
    {
        return $this->inner->getCurrencySymbolByCurrency($currencyCode ?: $this->getCurrency());
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
