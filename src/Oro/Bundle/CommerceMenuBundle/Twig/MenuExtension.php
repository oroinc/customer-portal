<?php

namespace Oro\Bundle\CommerceMenuBundle\Twig;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\MatcherInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Provides Twig functions to work with storefront menus:
 *   - oro_commercemenu_is_current
 *   - oro_commercemenu_is_ancestor
 *   - oro_commercemenu_get_url
 */
class MenuExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    /** @var ContainerInterface */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return MatcherInterface
     */
    protected function getMatcher()
    {
        return $this->container->get('knp_menu.matcher');
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
        ];
    }

    /**
     * @param ItemInterface $item
     *
     * @return bool
     */
    public function isCurrent(ItemInterface $item)
    {
        return $this->getMatcher()->isCurrent($item);
    }

    /**
     * @param ItemInterface $item
     *
     * @return bool
     */
    public function isAncestor(ItemInterface $item)
    {
        return $this->getMatcher()->isAncestor($item);
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function getUrl($url)
    {
        $result = parse_url($url);
        if (array_key_exists('host', $result) || array_key_exists('scheme', $result)) {
            return $url;
        }
        /** @var RequestStack $requestStack */
        $requestStack = $this->container->get('request_stack');
        $request = $requestStack->getCurrentRequest();

        $baseUrl = $request->getBaseUrl();

        // Url is already with base url so we use it as is
        // 0 - means we check occurrences at the very beginning of the url
        if ($baseUrl && stripos($url, $baseUrl) === 0) {
            return $url;
        }

        if (0 !== strpos($url, '/')) {
            $url = '/' . $url;
        }

        return $request->getUriForPath($url);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return [
            'knp_menu.matcher' => MatcherInterface::class,
            'request_stack' => RequestStack::class,
        ];
    }
}
