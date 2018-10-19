<?php

namespace Oro\Bundle\FrontendBundle\Provider;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\FrontendLocalizationBundle\Manager\UserLocalizationManager;
use Oro\Bundle\LocaleBundle\DependencyInjection\Configuration;
use Oro\Bundle\LocaleBundle\Provider\BasePreferredLanguageProvider;

/**
 * Default frontend language provider is used as a fallback for entities which are not supported by other providers.
 * Should be added with the priority to be after main provider and before default one.
 */
class DefaultFrontendPreferredLanguageProvider extends BasePreferredLanguageProvider
{
    /**
     * @var UserLocalizationManager
     */
    private $userLocalizationManager;

    /**
     * @var FrontendHelper
     */
    private $frontendHelper;

    /**
     * @param UserLocalizationManager $userLocalizationManager
     * @param FrontendHelper $frontendHelper
     */
    public function __construct(UserLocalizationManager $userLocalizationManager, FrontendHelper $frontendHelper)
    {
        $this->userLocalizationManager = $userLocalizationManager;
        $this->frontendHelper = $frontendHelper;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($entity): bool
    {
        return $this->frontendHelper->isFrontendRequest();
    }

    /**
     * {@inheritDoc}
     */
    public function getPreferredLanguageForEntity($entity): string
    {
        $localization = $this->userLocalizationManager->getCurrentLocalization();

        return $localization ? $localization->getLanguageCode() : Configuration::DEFAULT_LANGUAGE;
    }
}
