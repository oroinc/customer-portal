<?php

namespace Oro\Bundle\FrontendBundle\Twig;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityConfigBundle\Provider\EntityUrlProviderInterface;
use Oro\Bundle\FrontendBundle\Provider\StorefrontEntityUrlProvider;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\UIBundle\ContentProvider\ContentProviderManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Provides frontend-related twig functions:
 *   - `is_frontend`
 *   - `oro_get_content`
 *   - `oro_storefront_entity_route` (storefront equivalent of `oro_entity_route`)
 *   - `oro_storefront_entity_view_link` (equivalent of `oro_entity_view_link` and `oro_entity_object_view_link`)
 *   - `oro_storefront_entity_index_link`
 */
class FrontendExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    #[\Override]
    public function getFunctions(): array
    {
        return [
            // Overrides `oro_default_page` declared in {@see \Oro\Bundle\UIBundle\Twig\UiExtension}.
            new TwigFunction('oro_default_page', [$this, 'getDefaultPage']),
            // Overrides `oro_get_content` declared in {@see \Oro\Bundle\UIBundle\Twig\UiExtension}.
            new TwigFunction('oro_get_content', [$this, 'getContent'], ['is_safe' => ['html']]),

            /** Equivalent of 'oro_entity_route', {@see \Oro\Bundle\EntityConfigBundle\Twig\ConfigExtension} */
            new TwigFunction('oro_storefront_entity_route', $this->getStorefrontEntityRoute(...)),

            /** Equivalent of 'oro_entity_view_link' and 'oro_entity_object_view_link',
             * {@see \Oro\Bundle\EntityConfigBundle\Twig\ConfigExtension} */
            new TwigFunction('oro_storefront_entity_view_link', $this->getStorefrontEntityViewLink(...)),

            new TwigFunction('oro_storefront_entity_index_link', $this->getStorefrontEntityIndexLink(...)),
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
    public function getContent(?array $additionalContent = null, ?array $keys = null): array
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

    public function getStorefrontEntityRoute(
        object|string $entity,
        string $routeType = EntityUrlProviderInterface::ROUTE_INDEX
    ): ?string {
        return $this->getFrontendEntityUrlProvider()->getRoute($entity, $routeType);
    }

    public function getStorefrontEntityViewLink(
        object|string $entity,
        int|string|null $id = null,
        array $extraRouteParams = []
    ): ?string {
        if (\is_string($entity) && null === $id) {
            throw new \InvalidArgumentException(
                'Entity ID must be specified, or pass an entity instance/proxy instead of a class name.'
            );
        }
        $entityId = \is_object($entity)
            ? $this->getDoctrineHelper()->getSingleEntityIdentifier($entity)
            : $id;

        return $this->getFrontendEntityUrlProvider()->getViewUrl($entity, $entityId, $extraRouteParams);
    }

    public function getStorefrontEntityIndexLink(object|string $entity, array $extraRouteParams = []): ?string
    {
        return $this->getFrontendEntityUrlProvider()->getIndexUrl($entity, $extraRouteParams);
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return [
            'oro_ui.content_provider.manager' => ContentProviderManager::class,
            'oro_frontend.content_provider.manager' => ContentProviderManager::class,
            RouterInterface::class,
            FrontendHelper::class,
            StorefrontEntityUrlProvider::class,
            DoctrineHelper::class
        ];
    }

    private function getContentProviderManager(): ContentProviderManager
    {
        return $this->getFrontendHelper()->isFrontendRequest()
            ? $this->container->get('oro_frontend.content_provider.manager')
            : $this->container->get('oro_ui.content_provider.manager');
    }

    private function getRouter(): RouterInterface
    {
        return $this->container->get(RouterInterface::class);
    }

    private function getFrontendHelper(): FrontendHelper
    {
        return $this->container->get(FrontendHelper::class);
    }

    private function getFrontendEntityUrlProvider(): StorefrontEntityUrlProvider
    {
        return $this->container->get(StorefrontEntityUrlProvider::class);
    }

    private function getDoctrineHelper(): DoctrineHelper
    {
        return $this->container->get(DoctrineHelper::class);
    }
}
