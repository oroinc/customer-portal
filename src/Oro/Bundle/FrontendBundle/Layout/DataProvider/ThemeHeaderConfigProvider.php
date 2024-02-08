<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Layout\DataProvider;

use Doctrine\Persistence\ManagerRegistry;
use Knp\Menu\ItemInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Oro\Bundle\CMSBundle\Entity\ContentBlock;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FrontendBundle\DependencyInjection\Configuration;
use Oro\Bundle\FrontendBundle\Model\QuickAccessButtonConfig;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

/**
 * Layout data provider for Header System Config options.
 */
class ThemeHeaderConfigProvider
{
    private ConfigManager $configManager;
    private AclHelper $aclHelper;
    private ManagerRegistry $registry;
    private MenuProviderInterface $menuProvider;

    public function __construct(
        ConfigManager $configManager,
        AclHelper $aclHelper,
        ManagerRegistry $registry,
        MenuProviderInterface $menuProvider,
    ) {
        $this->configManager = $configManager;
        $this->aclHelper = $aclHelper;
        $this->registry = $registry;
        $this->menuProvider = $menuProvider;
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
            return $this->registry
                    ->getRepository(ContentBlock::class)
                    ->getContentBlockAliasById($configValue, $this->aclHelper) ?? '';
        }

        return '';
    }

    public function getQuickAccessButton(): ?ItemInterface
    {
        /** @var QuickAccessButtonConfig $configValue */
        $configValue = $this->configManager->get(
            Configuration::getConfigKeyByName(Configuration::QUICK_ACCESS_BUTTON)
        );

        if ($configValue?->getType()) {
            $menu = $this->menuProvider->get('quick_access_button_menu');

            return $menu->getExtra(QuickAccessButtonConfig::MENU_NOT_RESOLVED, false) ? null : $menu;
        }

        return null;
    }

    public function getQuickAccessButtonLabel(): ?string
    {
        return $this->getQuickAccessButton()?->getLabel();
    }
}
