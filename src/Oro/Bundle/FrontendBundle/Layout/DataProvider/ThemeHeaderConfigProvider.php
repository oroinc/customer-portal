<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Layout\DataProvider;

use Doctrine\Persistence\ManagerRegistry;
use Knp\Menu\ItemInterface;
use Oro\Bundle\CMSBundle\Entity\ContentBlock;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FrontendBundle\DependencyInjection\Configuration;
use Oro\Bundle\FrontendBundle\Provider\QuickAccessButtonDataProvider;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

/**
 * Layout data provider for Header System Config options.
 */
class ThemeHeaderConfigProvider
{
    private ConfigManager $configManager;
    private AclHelper $aclHelper;
    private ManagerRegistry $doctrine;
    private QuickAccessButtonDataProvider $quickAccessButtonDataProvider;

    public function __construct(
        ConfigManager $configManager,
        AclHelper $aclHelper,
        ManagerRegistry $doctrine,
        QuickAccessButtonDataProvider $quickAccessButtonDataProvider,
    ) {
        $this->configManager = $configManager;
        $this->aclHelper = $aclHelper;
        $this->doctrine = $doctrine;
        $this->quickAccessButtonDataProvider = $quickAccessButtonDataProvider;
    }

    /**
     * Returns the Alias of configured promotional content block or an empty string
     *
     * @see \Oro\Bundle\CMSBundle\Layout\DataProvider\ContentBlockDataProvider::getContentBlockView
     */
    public function getPromotionalBlockAlias(): string
    {
        $configValue = $this->configManager->get(
            Configuration::getConfigKeyByName(Configuration::PROMOTIONAL_CONTENT)
        );

        if ($configValue) {
            return $this->doctrine
                    ->getRepository(ContentBlock::class)
                    ->getContentBlockAliasById($configValue, $this->aclHelper) ?? '';
        }

        return '';
    }

    public function getQuickAccessButton(): ?ItemInterface
    {
        $configValue = $this->configManager->get(
            Configuration::getConfigKeyByName(Configuration::QUICK_ACCESS_BUTTON)
        );

        return $configValue ? $this->quickAccessButtonDataProvider->getMenu($configValue) : null;
    }

    public function getQuickAccessButtonLabel(): ?string
    {
        $configValue = $this->configManager->get(
            Configuration::getConfigKeyByName(Configuration::QUICK_ACCESS_BUTTON)
        );

        return $configValue ? $this->quickAccessButtonDataProvider->getLabel($configValue) : null;
    }
}
