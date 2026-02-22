<?php

namespace Oro\Bundle\WebsiteBundle\Twig;

use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Provides a Twig function to retrieve the current website information:
 *   - oro_website_get_current_website
 *
 * Provides Twig functions to retrieve website path values:
 *   - website_path
 *   - website_secure_path
 */
class WebsiteExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    #[\Override]
    public function getFunctions()
    {
        return [
            new TwigFunction('oro_website_get_current_website', [$this, 'getCurrentWebsite']),
            new TwigFunction('website_path', [$this, 'getWebsitePath']),
            new TwigFunction('website_secure_path', [$this, 'getWebsiteSecurePath'])
        ];
    }

    public function getCurrentWebsite(): ?Website
    {
        return $this->getWebsiteManager()->getCurrentWebsite();
    }

    public function getWebsitePath(string $route, array $routeParams = [], ?Website $website = null): string
    {
        return $this->getWebsiteUrlResolver()->getWebsitePath($route, $routeParams, $website);
    }

    public function getWebsiteSecurePath(string $route, array $routeParams = [], ?Website $website = null): string
    {
        return $this->getWebsiteUrlResolver()->getWebsiteSecurePath($route, $routeParams, $website);
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return [
            WebsiteUrlResolver::class,
            WebsiteManager::class
        ];
    }

    private function getWebsiteUrlResolver(): WebsiteUrlResolver
    {
        return $this->container->get(WebsiteUrlResolver::class);
    }

    private function getWebsiteManager(): WebsiteManager
    {
        return $this->container->get(WebsiteManager::class);
    }
}
