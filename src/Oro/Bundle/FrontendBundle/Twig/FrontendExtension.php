<?php

namespace Oro\Bundle\FrontendBundle\Twig;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Psr\Container\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Provides frontend-related twig functions:
 *   - is_frontend
 */
class FrontendExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    private function getFrontendHelper(): FrontendHelper
    {
        return $this->container->get(FrontendHelper::class);
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

    public function getDefaultPage(): string
    {
        return $this->container->get(RouterInterface::class)
            ->generate($this->getFrontendHelper()->isFrontendRequest() ? 'oro_frontend_root' : 'oro_default');
    }

    /**
     * {@inheritdoc]
     */
    public static function getSubscribedServices(): array
    {
        return [
            RouterInterface::class,
            FrontendHelper::class,
        ];
    }
}
