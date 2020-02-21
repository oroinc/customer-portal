<?php

namespace Oro\Bundle\FrontendBundle\Twig;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Psr\Container\ContainerInterface;
use Twig\TwigFunction;

/**
 * Provides frontend-related twig functions:
 *   - is_frontend
 */
class FrontendExtension extends \Twig_Extension
{
    /** @var ContainerInterface */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return FrontendHelper
     */
    private function getFrontendHelper(): FrontendHelper
    {
        return $this->container->get('oro_frontend.request.frontend_helper');
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('oro_default_page', [$this, 'getDefaultPage']),
        ];
    }

    /**
     * @return string
     */
    public function getDefaultPage(): string
    {
        return $this->container->get('router')
            ->generate($this->getFrontendHelper()->isFrontendRequest() ? 'oro_frontend_root' : 'oro_default');
    }
}
