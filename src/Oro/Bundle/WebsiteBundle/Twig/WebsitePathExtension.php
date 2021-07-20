<?php

namespace Oro\Bundle\WebsiteBundle\Twig;

use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Provides Twig functions to retrieve website path values:
 *   - website_path
 *   - website_secure_path
 */
class WebsitePathExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    const NAME = 'oro_website_path';

    /** @var ContainerInterface */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return WebsiteUrlResolver
     */
    protected function getWebsiteUrlResolver()
    {
        return $this->container->get('oro_website.resolver.website_url_resolver');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('website_path', [$this, 'getWebsitePath']),
            new TwigFunction('website_secure_path', [$this, 'getWebsiteSecurePath'])
        ];
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
            'oro_website.resolver.website_url_resolver' => WebsiteUrlResolver::class,
        ];
    }
}
