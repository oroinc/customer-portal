<?php

namespace Oro\Bundle\FrontendBundle\Provider;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\FrontendLocalizationBundle\Manager\UserLocalizationManagerInterface;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Provider\AbstractPreferredLocalizationProvider;

/**
 * Default frontend localization provider is used as a fallback for entities which are not supported by other providers.
 * Should be added with the priority to be after main provider and before default one.
 */
class DefaultFrontendPreferredLocalizationProvider extends AbstractPreferredLocalizationProvider
{
    /**
     * @var UserLocalizationManagerInterface|null
     */
    private $userLocalizationManager;

    /**
     * @var FrontendHelper|null
     */
    private $frontendHelper;

    /**
     * @param UserLocalizationManagerInterface $userLocalizationManager
     * @param FrontendHelper $frontendHelper
     */
    public function __construct(
        ?UserLocalizationManagerInterface $userLocalizationManager,
        ?FrontendHelper $frontendHelper
    ) {
        $this->userLocalizationManager = $userLocalizationManager;
        $this->frontendHelper = $frontendHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($entity): bool
    {
        return $this->userLocalizationManager && $this->frontendHelper
            && $this->frontendHelper->isFrontendRequest();
    }

    protected function getPreferredLocalizationForEntity($entity): ?Localization
    {
        return $this->userLocalizationManager->getCurrentLocalization();
    }
}
