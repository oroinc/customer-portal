<?php

namespace Oro\Bundle\WebsiteBundle\Helper;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;

/**
 * The utility class that helps to check if an entity is a website aware.
 */
class WebsiteAwareEntityHelper
{
    private ConfigManager $configManager;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    public function isWebsiteAware(object|string $objectOrClass): bool
    {
        $entityClass = \is_object($objectOrClass) ? ClassUtils::getClass($objectOrClass) : $objectOrClass;
        if (!$this->configManager->hasConfig($entityClass)) {
            return false;
        }

        return $this->configManager->getEntityConfig('website', $entityClass)->is('is_website_aware');
    }
}
