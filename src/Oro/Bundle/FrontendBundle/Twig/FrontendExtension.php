<?php

namespace Oro\Bundle\FrontendBundle\Twig;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\UIBundle\ContentProvider\ContentProviderManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Provides frontend-related twig functions:
 *   - is_frontend
 *   - oro_get_content
 */
class FrontendExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    /** @var ContainerInterface */
    private $container;

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
            // Overrides `oro_default_page` declared in {@see \Oro\Bundle\UIBundle\Twig\UiExtension}.
            new TwigFunction('oro_default_page', [$this, 'getDefaultPage']),
            // Overrides `oro_get_content` declared in {@see \Oro\Bundle\UIBundle\Twig\UiExtension}.
            new TwigFunction('oro_get_content', [$this, 'getContent'], ['is_safe' => ['html']]),
        ];
    }

    public function getDefaultPage(): string
    {
        return $this->getRouter()->generate(
            $this->getFrontendHelper()->isFrontendRequest() ? 'oro_frontend_root' : 'oro_default'
        );
    }

    /**
     * @param array|null $additionalContent
     * @param array<string>|null $keys
     *
     * @return array<string,mixed>
     */
    public function getContent(array $additionalContent = null, array $keys = null): array
    {
        $content = $this->getContentProviderManager()->getContent($keys);
        if ($additionalContent) {
            $content = array_merge($content, $additionalContent);
        }
        if ($keys) {
            $content = array_intersect_key($content, array_combine($keys, $keys));
        }

        return $content;
    }

    /**
     * {@inheritdoc]
     */
    public static function getSubscribedServices(): array
    {
        return [
            RouterInterface::class,
            FrontendHelper::class,
            'oro_ui.content_provider.manager',
            'oro_frontend.content_provider.manager',
        ];
    }

    private function getRouter(): RouterInterface
    {
        return $this->container->get(RouterInterface::class);
    }

    private function getFrontendHelper(): FrontendHelper
    {
        return $this->container->get(FrontendHelper::class);
    }

    private function getContentProviderManager(): ContentProviderManager
    {
        if ($this->getFrontendHelper()->isFrontendRequest()) {
            return $this->container->get('oro_frontend.content_provider.manager');
        }

        return $this->container->get('oro_ui.content_provider.manager');
    }
}
