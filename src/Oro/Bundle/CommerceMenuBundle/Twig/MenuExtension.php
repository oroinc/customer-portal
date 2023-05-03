<?php

namespace Oro\Bundle\CommerceMenuBundle\Twig;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\MatcherInterface;
use Oro\Bundle\CommerceMenuBundle\Layout\MenuItemRenderer;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Provides Twig functions to work with storefront menus:
 *   - oro_commercemenu_is_current
 *   - oro_commercemenu_is_ancestor
 *   - oro_commercemenu_get_url
 *   - oro_commercemenu_render_menu_item
 */
class MenuExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    private ContainerInterface $container;
    private ?MatcherInterface $matcher = null;
    private ?RequestStack $requestStack = null;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritDoc
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('oro_commercemenu_is_current', [$this, 'isCurrent']),
            new TwigFunction('oro_commercemenu_is_ancestor', [$this, 'isAncestor']),
            new TwigFunction('oro_commercemenu_get_url', [$this, 'getUrl']),
            new TwigFunction(
                'oro_commercemenu_render_menu_item',
                [$this, 'renderMenuItem'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    public function isCurrent(ItemInterface $item): bool
    {
        return $this->getMatcher()->isCurrent($item);
    }

    public function isAncestor(ItemInterface $item): bool
    {
        return $this->getMatcher()->isAncestor($item);
    }

    public function getUrl(?string $url): string
    {
        $url = (string)$url;
        $result = parse_url($url);
        if (\array_key_exists('host', $result) || \array_key_exists('scheme', $result)) {
            return $url;
        }

        $request = $this->getRequest();
        if (null === $request) {
            return $url;
        }

        $baseUrl = $request->getBaseUrl();

        // Url is already with base url so we use it as is
        // 0 - means we check occurrences at the very beginning of the url
        if ($baseUrl && stripos($url, $baseUrl) === 0) {
            return $url;
        }

        if (!str_starts_with($url, '/')) {
            $url = '/' . $url;
        }

        return $request->getUriForPath($url);
    }

    public function renderMenuItem(ItemInterface $menuItem): string
    {
        return $this->container->get('oro_commerce_menu.layout.menu_item_renderer')->render($menuItem);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return [
            'knp_menu.matcher' => MatcherInterface::class,
            RequestStack::class,
            'oro_commerce_menu.layout.menu_item_renderer' => MenuItemRenderer::class,
        ];
    }

    private function getMatcher(): MatcherInterface
    {
        if (null === $this->matcher) {
            $this->matcher = $this->container->get('knp_menu.matcher');
        }

        return $this->matcher;
    }

    private function getRequest(): ?Request
    {
        if (null === $this->requestStack) {
            $this->requestStack = $this->container->get(RequestStack::class);
        }

        return $this->requestStack->getCurrentRequest();
    }
}
