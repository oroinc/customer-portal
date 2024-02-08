<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Menu\Frontend;

use Knp\Menu\ItemInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FrontendBundle\DependencyInjection\Configuration;
use Oro\Bundle\FrontendBundle\Model\QuickAccessButtonConfig;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;

/**
 * Builds Quick Access Button menu items based on frontend menu configuration.
 */
class QuickAccessButtonFrontendMenuMenuBuilder implements BuilderInterface
{
    private ConfigManager $configManager;
    private BuilderInterface $menuBuilder;

    public function __construct(
        ConfigManager $configManager,
        BuilderInterface $menuBuilder,
    ) {
        $this->configManager = $configManager;
        $this->menuBuilder = $menuBuilder;
    }

    public function build(ItemInterface $menu, array $options = [], $alias = null): void
    {
        if ('quick_access_button_menu' !== $alias) {
            return;
        }

        /** @var QuickAccessButtonConfig $configValue */
        $configValue = $this->configManager->get(
            Configuration::getConfigKeyByName(Configuration::QUICK_ACCESS_BUTTON)
        );

        if (QuickAccessButtonConfig::TYPE_MENU !== $configValue?->getType()) {
            return;
        }

        $menuName = $configValue->getMenu();
        if ('quick_access_button_menu' === $menuName) {
            $menu->setExtra(QuickAccessButtonConfig::MENU_NOT_RESOLVED, true);

            return;
        }
        $this->menuBuilder->build(
            $menu,
            array_merge($options, ['check_access_not_logged_in' => true]),
            $configValue->getMenu()
        );
        /** TODO: Add functionality to get label for root menu at BB-23579 */
        $menu->setLabel($configValue->getMenu());

        if (count($menu->getChildren()) === 0 && null == $menu->getUri()) {
            $menu->setExtra(QuickAccessButtonConfig::MENU_NOT_RESOLVED, true);

            return;
        }
    }
}
