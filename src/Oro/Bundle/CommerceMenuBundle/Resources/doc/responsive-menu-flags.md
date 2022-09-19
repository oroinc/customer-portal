Responsive Menu Flags
=====================

To allow user to specify additional visibility restrictions, menu item form has been extended with the following fields:
1. User Agent: collection of groups divided with OR of user agent expressions divided with AND.
If a user agent string does not satisfy collection of user-agent expressions specified for menu item, it gets display=false property and is not rendered. See `Oro\Bundle\CommerceMenuBundle\Menu\ConditionEvaluator\UserAgentConditionsEvaluator` for details.

2. Exclude on Screens: multiselect with the list of screens available in application.
Menu items are not excluded from output and are rendered by backend all the same, because it is impossible to detect user screen during PHP execution. Thus, menu items are excluded from display via CSS: menu item container is marked with additional CSS classes corresponding to screens which are selected in "Exclude on Screens" field. See `Oro\Bundle\CommerceMenuBundle\Builder\MenuScreensConditionBuilder` for details about how classes are added to menu item container.

Screens which are listed in "Exclude on Screens" field are collected from all themes in application. Available screens and corresponding CSS classes can be defined by each theme in screens.yml file in config/ directory of theme. E.g.

```yaml
screens:
    # Machine name of screen.
    desktop:
        # Label of screen which will be displayed in "Exclude on Screens" field.
        label: 'Laptops and desktops with 13 in. + screens'
        # CSS class which will be added to menu item container to exclude it on selected screen.
        hidingCssClass: 'hide-on-desktop'
```

In general, CSS class (`hidingCssClass`) which hides a menu item should use Media Queries functionality. Currently, we have [a list of media breakpoints](https://github.com/oroinc/platform/blob/master/src/Oro/Bundle/UIBundle/Resources/public/default/scss/settings/_breakpoints.scss) - for each breakpoint we [automatically generate corresponding hiding CSS class](https://github.com/oroinc/platform/blob/master/src/Oro/Bundle/UIBundle/Resources/public/blank/scss/base/base.scss#L7). Look [here](https://github.com/oroinc/customer-portal/blob/master/src/Oro/Bundle/FrontendBundle/Resources/doc/frontendStylesCustomization.md#how-to-change-media-breakpoints) to discover how to add new media breakpoints. So, if you want to add a new screen, you should add it to screens.yml of your theme, add new media breakpoint and specify for your screen a hiding CSS class, which basically is named as `hide-on-{BREAKPOINT-NAME}`.
