<?php

namespace Oro\Bundle\CommerceMenuBundle\Twig;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\MatcherInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class MenuExtension extends \Twig_Extension
{
    const NAME = 'oro_commercemenu';

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
     * @return MatcherInterface
     */
    protected function getMatcher()
    {
        return $this->container->get('knp_menu.matcher');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @inheritDoc
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('oro_commercemenu_is_current', [$this, 'isCurrent']),
            new \Twig_SimpleFunction('oro_commercemenu_is_ancestor', [$this, 'isAncestor']),
            new \Twig_SimpleFunction('oro_commercemenu_get_url', [$this, 'getUrl']),
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

        //Url is already with base url so we use it as is
        if ($baseUrl && stripos($url, $baseUrl) !== false) {
            return $url;
        }

        if (0 !== strpos($url, '/')) {
            $url = '/' . $url;
        }

        return $request->getUriForPath($url);
    }
}
