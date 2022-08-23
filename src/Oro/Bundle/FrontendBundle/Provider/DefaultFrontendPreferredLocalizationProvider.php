<?php

namespace Oro\Bundle\FrontendBundle\Provider;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Provider\AbstractPreferredLocalizationProvider;
use Oro\Bundle\LocaleBundle\Provider\LocalizationProviderInterface;

/**
 * Default frontend localization provider is used as a fallback for entities which are not supported by other providers.
 * Should be added with the priority to be after main provider and before default one.
 */
class DefaultFrontendPreferredLocalizationProvider extends AbstractPreferredLocalizationProvider
{
    private ?LocalizationProviderInterface $localizationProvider;

    private ?FrontendHelper $frontendHelper;

    public function __construct(
        ?LocalizationProviderInterface $localizationProvider,
        ?FrontendHelper $frontendHelper
    ) {
        $this->localizationProvider = $localizationProvider;
        $this->frontendHelper = $frontendHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($entity): bool
    {
        return $this->localizationProvider && $this->frontendHelper
            && $this->frontendHelper->isFrontendRequest();
    }

    protected function getPreferredLocalizationForEntity($entity): ?Localization
    {
        return $this->localizationProvider->getCurrentLocalization();
    }
}
