<?php

namespace Oro\Bundle\CommerceMenuBundle\Layout\DataProvider;

use Knp\Menu\ItemInterface;
use Knp\Menu\Provider\MenuProviderInterface;

class MenuProvider
{
    /** @var MenuProviderInterface */
    private $provider;

    public function __construct(MenuProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Retrieves item in the menu, eventually using the menu provider.
     *
     * @param string $menuName
     * @param array  $options
     *
     * @return ItemInterface
     */
    public function getMenu($menuName, array $options = ['check_access_not_logged_in' => true])
    {
        return $this->provider->get($menuName, $options);
    }
}
