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
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * @param string       $route
     * @param array        $routeParams
     * @param Website|null $website
     *
     * @return string
     */
    public function getWebsitePath($route, array $routeParams = [], Website $website = null)
    {
        return $this->getWebsiteUrlResolver()->getWebsitePath($route, $routeParams, $website);
    }

    /**
     * @param string       $route
     * @param array        $routeParams
     * @param Website|null $website
     *
     * @return string
     */
    public function getWebsiteSecurePath($route, array $routeParams = [], Website $website = null)
    {
        return $this->getWebsiteUrlResolver()->getWebsiteSecurePath($route, $routeParams, $website);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return [
            'oro_website.manager' => WebsiteManager::class,
            'oro_website.resolver.website_url_resolver' => WebsiteUrlResolver::class,
        ];
    }

    private function getWebsiteManager(): WebsiteManager
    {
        return $this->container->get('oro_website.manager');
    }

    private function getWebsiteUrlResolver(): WebsiteUrlResolver
    {
        return $this->container->get('oro_website.resolver.website_url_resolver');
    }
}
