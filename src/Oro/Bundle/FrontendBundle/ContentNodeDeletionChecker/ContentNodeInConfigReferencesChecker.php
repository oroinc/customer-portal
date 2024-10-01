<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\ContentNodeDeletionChecker;

use Oro\Bundle\FrontendBundle\Model\QuickAccessButtonConfig;
use Oro\Bundle\LayoutBundle\Layout\Extension\ThemeConfiguration;
use Oro\Bundle\ThemeBundle\Provider\ThemeConfigurationProvider;
use Oro\Bundle\WebCatalogBundle\ContentNodeDeletionChecker\ContentNodeDeletionCheckerInterface;
use Oro\Bundle\WebCatalogBundle\Context\NotDeletableContentNodeResult;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Bundle\WebsiteBundle\Provider\WebsiteProviderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Checks on references in Commerce Theme Header Config for Content Node
 */
class ContentNodeInConfigReferencesChecker implements ContentNodeDeletionCheckerInterface
{
    public function __construct(
        private TranslatorInterface $translator,
        private WebsiteProviderInterface $websiteProvider,
        private ThemeConfigurationProvider $themeConfigurationProvider
    ) {
    }

    #[\Override]
    public function check(ContentNode $contentNode): ?NotDeletableContentNodeResult
    {
        $result = new NotDeletableContentNodeResult();
        $key = ThemeConfiguration::buildOptionKey('header', 'quick_access_button');
        $configs = [];

        foreach ($this->websiteProvider->getWebsites() as $website) {
            $configs[] = $this->themeConfigurationProvider->getThemeConfigurationOption($key, $website);
        }

        $filteredConfigs = array_filter(
            $configs,
            function (?QuickAccessButtonConfig $config) use ($contentNode) {
                return $config instanceof QuickAccessButtonConfig &&
                    $config->getType() === QuickAccessButtonConfig::TYPE_WEB_CATALOG_NODE &&
                    $config->getWebCatalogNode() == $contentNode->getId();
            }
        );

        if ($filteredConfigs) {
            $result->setWarningMessageParams([
                '%key%' => $key,
                '%subject%' => $this->translator->trans('oro.webcatalog.system_configuration.label')
            ]);

            return $result;
        }

        return null;
    }
}
