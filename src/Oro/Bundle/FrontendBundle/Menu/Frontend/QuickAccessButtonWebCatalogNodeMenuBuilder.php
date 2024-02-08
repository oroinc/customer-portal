<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Menu\Frontend;

use Doctrine\Persistence\ManagerRegistry;
use Knp\Menu\ItemInterface;
use Oro\Bundle\CommerceMenuBundle\Handler\SubFolderUriHandler;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FrontendBundle\DependencyInjection\Configuration;
use Oro\Bundle\FrontendBundle\Model\QuickAccessButtonConfig;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\NavigationBundle\Entity\MenuUpdateInterface;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;
use Oro\Bundle\WebCatalogBundle\Cache\ResolvedData\ResolvedContentNode;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Bundle\WebCatalogBundle\Menu\MenuContentNodesProviderInterface;

/**
 * Builds Quick Access Button menu items based on web catalog node configuration.
 */
class QuickAccessButtonWebCatalogNodeMenuBuilder implements BuilderInterface
{
    private ConfigManager $configManager;
    private MenuContentNodesProviderInterface $menuContentNodesProvider;
    private LocalizationHelper $localizationHelper;
    private SubFolderUriHandler $uriHandler;
    private ManagerRegistry $doctrine;

    public function __construct(
        ConfigManager $configManager,
        MenuContentNodesProviderInterface $menuContentNodesProvider,
        LocalizationHelper $localizationHelper,
        SubFolderUriHandler $uriHandler,
        ManagerRegistry $doctrine,
    ) {
        $this->configManager = $configManager;
        $this->menuContentNodesProvider = $menuContentNodesProvider;
        $this->localizationHelper = $localizationHelper;
        $this->uriHandler = $uriHandler;
        $this->doctrine = $doctrine;
    }

    public function build(ItemInterface $menu, array $options = [], $alias = null): void
    {
        if ('quick_access_button_menu' !== $alias) {
            return;
        }

        /** @var QuickAccessButtonConfig|null $configValue */
        $configValue = $this->configManager->get(
            Configuration::getConfigKeyByName(Configuration::QUICK_ACCESS_BUTTON)
        );

        if (QuickAccessButtonConfig::TYPE_WEB_CATALOG_NODE !== $configValue?->getType()) {
            return;
        }

        $nodeId = $configValue->getWebCatalogNode();

        if (!$nodeId) {
            $menu->setExtra(QuickAccessButtonConfig::MENU_NOT_RESOLVED, true);

            return;
        }

        $node = $this->doctrine->getRepository(ContentNode::class)->find($configValue->getWebCatalogNode());
        $resolvedNode = $node ? $this->menuContentNodesProvider->getResolvedContentNode($node, [
            'tree_depth' => 0,
        ]) : null;

        if (!$resolvedNode) {
            $menu->setExtra(QuickAccessButtonConfig::MENU_NOT_RESOLVED, true);

            return;
        }

        $localization = $this->localizationHelper->getCurrentLocalization();
        $menu->setLabel($this->getLabel($resolvedNode, $localization));
        $menu->setExtra(MenuUpdateInterface::IS_TRANSLATE_DISABLED, true);
        $menu->setUri($this->getUri($resolvedNode, $localization));
    }

    private function getUri(ResolvedContentNode $resolvedNode, ?Localization $localization): string
    {
        $uri = (string) $this->localizationHelper
            ->getLocalizedValue($resolvedNode->getResolvedContentVariant()->getLocalizedUrls(), $localization);

        return $this->uriHandler->hasSubFolder() ? $this->uriHandler->handle($uri) : $uri;
    }

    private function getLabel(ResolvedContentNode $resolvedNode, ?Localization $localization): string
    {
        return (string) $this->localizationHelper->getLocalizedValue($resolvedNode->getTitles(), $localization);
    }
}
