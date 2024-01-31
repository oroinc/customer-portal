<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Layout\DataProvider;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CMSBundle\Entity\ContentBlock;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FrontendBundle\DependencyInjection\Configuration;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

/**
 * Layout data provider for Header System Config options.
 */
class ThemeHeaderConfigProvider
{
    private ConfigManager $configManager;
    private AclHelper $aclHelper;
    private ManagerRegistry $registry;

    public function __construct(
        ConfigManager $configManager,
        AclHelper $aclHelper,
        ManagerRegistry $registry
    ) {
        $this->configManager = $configManager;
        $this->aclHelper = $aclHelper;
        $this->registry = $registry;
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
}
