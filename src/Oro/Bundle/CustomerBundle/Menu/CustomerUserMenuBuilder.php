<?php

namespace Oro\Bundle\CustomerBundle\Menu;

use Knp\Menu\ItemInterface;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;

/**
 * Builds the customer user menu with logout functionality.
 *
 * This menu builder constructs the dropdown menu for authenticated customer users,
 * adding a divider and a logout link to allow users to end their session.
 */
class CustomerUserMenuBuilder implements BuilderInterface
{
    #[\Override]
    public function build(ItemInterface $menu, array $options = array(), $alias = null)
    {
        $menu->setExtra('type', 'dropdown');

        $menu
            ->addChild('divider-customer-user-before-logout')
            ->setLabel('')
            ->setExtra('divider', true);

        $menu
            ->addChild(
                'oro.customer.menu.customer_user_logout.label',
                [
                    'route'          => 'oro_customer_customer_user_security_logout',
                    'linkAttributes' => ['class' => 'no-hash']
                ]
            );
    }
}
