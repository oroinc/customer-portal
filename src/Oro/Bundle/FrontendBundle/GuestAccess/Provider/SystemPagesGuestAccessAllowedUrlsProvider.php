<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\GuestAccess\Provider;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FrontendBundle\DependencyInjection\Configuration;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;

/**
 * Provides guest access allowed URLs for configured system pages.
 */
class SystemPagesGuestAccessAllowedUrlsProvider implements GuestAccessAllowedUrlsProviderInterface
{
    public function __construct(
        protected ConfigManager $configManager,
        protected RouterInterface $router
    ) {
    }

    public function getAllowedUrlsPatterns(): array
    {
        $patterns = [];
        $systemPages = $this->configManager->get(
            Configuration::getConfigKeyByName(Configuration::GUEST_ACCESS_ALLOWED_SYSTEM_PAGES)
        );

        if (!\is_array($systemPages)) {
            return $patterns;
        }

        foreach ($systemPages as $routeName) {
            try {
                $url = $this->router->generate($routeName);
                $patterns[] = '^' . \preg_quote($url) . '$';
            } catch (RouteNotFoundException $e) {
                // Skip routes that don't exist
                continue;
            }
        }

        return $patterns;
    }
}
