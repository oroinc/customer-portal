# OroCommerceMenuBundle

OroCommerceMenuBundle uses [OroNavigationBundle](https://github.com/oroinc/platform/tree/master/src/Oro/Bundle/NavigationBundle) features to enable navigation menus in the Oro application storefront and allows admin users to configure frontend menu items on the global level (for the entire system), for individual customers, and customer groups.

## General

Example usage:

```
# DemoBundle\Resources\config\oro\navigation.yml

navigation:
    menu_config:
        items:
            first_menu_item:
                label: 'First Menu Item'
                route: '#'
                extras:
                    position: 10
            second_menu_item:
                label: 'Second Menu Item'
                route: '#'
                extras:
                    position: 20

        tree:
            top_nav:
                scope_type: menu_frontend_visibility                    # identifier scope type for menus using in frontend
                children:
                    first_menu_item ~
                    second_menu_item ~
```

For more information, see [CommerceMenuBundle documentation](https://doc.oroinc.com/bundles/commerce/CommerceMenuBundle/) and [NavigationBundle documentation](https://doc.oroinc.com/bundles/platform/NavigationBundle/).


## Sections:

* [Responsive Menu Flags](https://github.com/oroinc/customer-portal/blob/master/src/Oro/Bundle/CommerceMenuBundle/Resources/doc/responsive-menu-flags.md)
