<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\ContentNodeDeletionChecker;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FrontendBundle\DependencyInjection\Configuration;
use Oro\Bundle\FrontendBundle\Model\QuickAccessButtonConfig;
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
    private const CONFIG_KEY = Configuration::ROOT_NODE . '.' . Configuration::QUICK_ACCESS_BUTTON;

    private TranslatorInterface $translator;
    private ConfigManager $configManager;
    private WebsiteProviderInterface $websiteProvider;

    public function __construct(
        TranslatorInterface $translator,
        ConfigManager $configManager,
        WebsiteProviderInterface $websiteProvider
    ) {
        $this->translator = $translator;
        $this->configManager = $configManager;
        $this->websiteProvider = $websiteProvider;
    }

    public function check(ContentNode $contentNode): ?NotDeletableContentNodeResult
    {
        $result = new NotDeletableContentNodeResult();

        $configs = $this->configManager->getValues(
            self::CONFIG_KEY,
            $this->websiteProvider->getWebsites(),
            false,
            true
        );
        $filteredConfigs = array_filter(
            $configs,
            function (array $config) use ($contentNode) {
                $value = $config['value'] ?? null;
                return $value instanceof QuickAccessButtonConfig
                    && $value->getType() === QuickAccessButtonConfig::TYPE_WEB_CATALOG_NODE
                    && $value->getWebCatalogNode() == $contentNode->getId();
            }
        );

        if ($filteredConfigs) {
            $result->setWarningMessageParams([
                '%key%' => self::CONFIG_KEY,
                '%subject%' => $this->translator->trans('oro.webcatalog.system_configuration.label')
            ]);

            return $result;
        }

        return null;
    }
}
