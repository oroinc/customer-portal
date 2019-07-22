<?php

namespace Oro\Bundle\WebsiteBundle\Twig;

use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Twig function to retrieve the current website information:
 *   - oro_website_get_current_website
 */
class OroWebsiteExtension extends \Twig_Extension
{
    const NAME = 'oro_website_extension';

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
     * @return WebsiteManager
     */
    protected function getWebsiteManager()
    {
        return $this->container->get('oro_website.manager');
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('oro_website_get_current_website', [$this, 'getCurrentWebsite'])
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @return Website
     */
    public function getCurrentWebsite()
    {
        return $this->getWebsiteManager()->getCurrentWebsite();
    }
}
