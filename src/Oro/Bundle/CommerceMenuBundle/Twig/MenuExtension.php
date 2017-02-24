<?php

namespace Oro\Bundle\CommerceMenuBundle\Twig;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\MatcherInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;

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
}
