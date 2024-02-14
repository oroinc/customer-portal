<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Provider;

use Knp\Menu\ItemInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Oro\Bundle\FrontendBundle\Model\QuickAccessButtonConfig;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\LocaleBundle\Helper\LocalizedValueExtractor;
use Oro\Bundle\UIBundle\Tools\HtmlTagHelper;

/**
 * Provides data based on given configuration
 */
class QuickAccessButtonDataProvider
{
    private MenuProviderInterface $menuProvider;
    private LocalizedValueExtractor $localizedValueExtractor;
    private LocalizationHelper $localizationHelper;
    private HtmlTagHelper $htmlTagHelper;

    public function __construct(
        MenuProviderInterface $menuProvider,
        LocalizedValueExtractor $localizedValueExtractor,
        LocalizationHelper $localizationHelper,
        HtmlTagHelper $htmlTagHelper,
    ) {
        $this->menuProvider = $menuProvider;
        $this->localizedValueExtractor = $localizedValueExtractor;
        $this->localizationHelper = $localizationHelper;
        $this->htmlTagHelper = $htmlTagHelper;
    }

    public function getLabel(QuickAccessButtonConfig $config): ?string
    {
        $label = $this->localizedValueExtractor->getLocalizedFallbackValue(
            $config->getLabel(),
            $this->localizationHelper->getCurrentLocalization()
        );

        return $label ? $this->htmlTagHelper->purify($label) : null;
    }

    public function getMenu(QuickAccessButtonConfig $config): ?ItemInterface
    {
        if ($config?->getType()) {
            $menu = $this->menuProvider->get('quick_access_button_menu', ['qab_config' => $config]);

            return $menu->getExtra(QuickAccessButtonConfig::MENU_NOT_RESOLVED, false) ? null : $menu;
        }

        return null;
    }
}
