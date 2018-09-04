<?php

namespace Oro\Bundle\WebsiteBundle\Twig;

use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Adds twig functions for url generation
 */
class WebsitePathExtension extends \Twig_Extension
{
    const NAME = 'oro_website_path';

    /** @var ContainerInterface */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
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
            new \Twig_SimpleFunction('website_path', [$this, 'getWebsitePath']),
            new \Twig_SimpleFunction('website_secure_path', [$this, 'getWebsiteSecurePath'])
        ];
    }

    /**
     * @param string       $route
     * @param array        $routeParams
     * @param Website|null $website
     *
     * @return string
     */
    public function getWebsitePath($route, array $routeParams, Website $website = null)
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
    public function getWebsiteSecurePath($route, array $routeParams, Website $website = null)
    {
        return $this->getWebsiteUrlResolver()->getWebsiteSecurePath($route, $routeParams, $website);
    }
}
