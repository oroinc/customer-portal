The upgrade instructions are available at [Oro documentation website](https://doc.oroinc.com/master/backend/setup/upgrade-to-new-version/).

The current file describes significant changes in the code that may affect the upgrade of your customizations.

## Changes in the Customer Portal package versions

- [5.1.0](#510-2023-03-31)
- [5.0.0](#500-2022-01-26)
- [4.2.0](#420-2020-01-29)
- [4.1.0](#410-2020-01-31)
- [4.0.0](#400-2019-07-31)
- [3.1.4](#314)
- [3.1.3](#313-2019-02-19)
- [3.1.2](#312-2019-02-05)
- [3.1.0](#310-2019-01-30)
- [3.0.0](#300-2018-07-27)
- [2.6.0](#260-2018-01-31)
- [2.5.0](#250-2017-11-30)
- [2.4.0](#240-2017-09-29)
- [2.3.0](#230-2017-07-28)
- [2.2.0](#220-2017-05-31)


## 5.1.0 (2023-03-31)
[Show detailed list of changes](incompatibilities-5-1.md)

### Added

#### CommerceMenuBundle
* Added `oro_commerce_menu.main_navigation_menu` setting to the system configuration and the ability to change main navigation menu on the storefront as per this setting.
* Added the ability to specify target type for root menu items via menu update form like for other menu items.
* Added `synthetic` field to `\Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate`.
* Added `maxTraverseLevel` field for `\Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate` to store the max depth for the content node or category menu items tree.
* Added `menuTemplate` field for `\Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate` to store the menu template name to use for when rendering a menu item.
* Added `maxTraverseLevel` field to the form `\Oro\Bundle\NavigationBundle\Form\Type\MenuUpdateType`. 
* Added `menuTemplate` field to the form `\Oro\Bundle\NavigationBundle\Form\Type\MenuUpdateType`.
* Added the menu builder `\Oro\Bundle\CommerceMenuBundle\Builder\CategoryTreeBuilder` to add the ability to fill the menu items of "Category" target type with their children as per `\Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate::$maxTraverseLevel` depth.
* Added the menu builder `\Oro\Bundle\CommerceMenuBundle\Builder\ContentNodeTreeBuilder` to add the ability to fill the menu items of "Content Node" target type with their children as per `\Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate::$maxTraverseLevel` depth.
* Added `menu_templates` theme configuration allowing to specify menu templates available for storefront menu items rendering.
* Added `\Oro\Bundle\CommerceMenuBundle\Provider\MenuTemplatesProvider` to collect available menu templates.
* Added `\Oro\Bundle\CommerceMenuBundle\Layout\MenuItemRenderer` to render a menu item with the specified menu template using layouts.
* Added the following layout block types with options:
  - `menu_list`: Container for rendering menu list
    + `layoutType`: **{default: null}** - Allows you to set a layout modifier. Requires a `class_prefix` value to be set
    + `tagName`: **{default: 'ul'}** - Specifies which html tag to use
  - `menu_item`: Menu item with actions, also allows you to render the next level menu list
    + `tagName`: **{default: 'li'}** - Specifies which html tag to use
    + `iconEnable`: **{default: true}** - Enable/Disable icon of the menu item
    + `linkEnable`: **{default: true}** - Enable/Disable link of the menu item
    + `tooltipEnable`: **{default: false}** - Enable/Disable tooltip for menu item
    + `dividerEnable`: **{default: true}** - Enable/Disable divider items
    + `imageViewAs`: **{default: 'line'} 'line'|'image'|false** - Show item with image as simple text item, item with image or disable
    + `attr`: **{default: null}** - Menu Item Attributes
    + `actions_attr`: **{default: null}** - Action Container Attributes
    + `tooltipTemplate`: Base HTML to use when creating the tooltip.
    + `link_attr`: **{default: null}** - Item menu link attributes
    + `button_attr`: **{default: null}** - Item Menu Button Attributes
    + `button_text_attr`: **{default: null}** - Item Menu Text Label Attributes
  - `menu_inner`: Menu item container for rendering next nested content
    + `layoutType`: **{default: null}** - Allows you to set a layout modifier. Requires a `class_prefix` value to be set
* Added a `scss` mixin `main-menu-stack-items` that provides a basic `scss` structure for a stack of menu items
* Added three different menu templates for menu items:
  - `list`: Simple list of menu
  - `tree`: Multi-column menu
  - `mega`: Multi-level menu with the ability to transition between levels with a slide effect on mobile devices, also a responsive view for desktop devices

### Changed

#### FrontendBundle

The widgets `collapse-widget`, `collapse-group-widget`, `rows-collapse-widget` were removed, use the `bootstrap-collapse` instead.
- As a result, you need to update your `html`:

  **layout.twig**
    ```diff
    - {% set collapseView = {
    -   storageKey: 'unique storage key',
    -   uid: 'unique storage key id',
    -   animationSpeed: 0,
    -   closeClass: 'overflows',
    -   forcedState: false,
    -   checkOverflow: false,
    -   open: false,
    -   keepState: false
    - } %}
    - <div class="collapse-block" data-page-component-collapse="{{ collapseView|json_encode }}">
    -   <div class="control-label" data-collapse-container>
    -     Neque porro quisquam est qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit
    -   </div>
    -   <a href="#" class="control-label toggle-more" data-collapse-trigger>{{ 'Show more'|trans }}</a>
    -   <a href="#" class="control-label toggle-less" data-collapse-trigger>{{ 'Show less'|trans }}</a>
    - </div>
    + {% set collapseId = 'collapse-'|uniqid %}
    + <div class="collapse-block">
    +   <div id="{{ collapseId }}" class="collapse-overflow collapse no-transition"
    +        data-collapsed-text="{{ 'Show more'|trans }}"
    +        data-expanded-text="{{ 'Show less'|trans }}"
    +        data-check-overflow="true"
    +        data-toggle="false"
    +        data-state-id="{{ 'unique storage key id' }}"
    +   >Neque porro quisquam est qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit...</div>
    +   <a href="#"
    +      role="button"
    +      class="collapse-toggle"
    +      data-toggle="collapse"
    +      data-target="{{ '#' ~ collapseId }}"
    +      aria-expanded="false"
    +      aria-controls="{{ collapseId }}"><span data-text>{{ 'Show more'|trans }}</span></a>
    + </div>
    ```

The 'data-dom-relocation' selector to specify options for `dom-relocation-view` was removed, use 'data-dom-relocation-options' instead.

The oro grid system was modernized and uses CSS grid under the hood.
- As a result, you need to update your `html`:

  **layout.twig**
    ```diff
    - <div class="grid">
    -   <div class="grid__row">
    -     <div class="grid__column grid__column--1">grid__column--1</div>
    -     <div class="grid__column grid__column--1">grid__column--1</div>
    -     <div class="grid__column grid__column--1">grid__column--1</div>
    -     <div class="grid__column grid__column--1">grid__column--1</div>
    -     <div class="grid__column grid__column--1">grid__column--1</div>
    -     <div class="grid__column grid__column--1">grid__column--1</div>
    -     <div class="grid__column grid__column--1">grid__column--1</div>
    -     <div class="grid__column grid__column--1">grid__column--1</div>
    -     <div class="grid__column grid__column--1">grid__column--1</div>
    -     <div class="grid__column grid__column--1">grid__column--1</div>
    -     <div class="grid__column grid__column--1">grid__column--1</div>
    -     <div class="grid__column grid__column--1">grid__column--1</div>
    -   </div>
    -   <div class="grid__row">
    -     <div class="grid__column grid__column--3">grid__column--3</div>
    -     <div class="grid__column grid__column--9">grid__column--9</div>
    -   </div>
    -   <div class="grid__row">
    -     <div class="grid__column grid__column--4">grid__column--4</div>
    -     <div class="grid__column grid__column--4">grid__column--4</div>
    -     <div class="grid__column grid__column--4">grid__column--4</div>
    -   </div>
    -   <div class="grid__row">
    -     <div class="grid__column grid__column--12">grid__column--12</div>
    -   </div>
    - </div>
    + <div class="grid">
    +   <div class="grid-col">1/12</div>
    +   <div class="grid-col">1/12</div>
    +   <div class="grid-col">1/12</div>
    +   <div class="grid-col">1/12</div>
    +   <div class="grid-col">1/12</div>
    +   <div class="grid-col">1/12</div>
    +   <div class="grid-col">1/12</div>
    +   <div class="grid-col">1/12</div>
    +   <div class="grid-col">1/12</div>
    +   <div class="grid-col">1/12</div>
    +   <div class="grid-col">1/12</div>
    +   <div class="grid-col">1/12</div>

    +   <div class="grid-col-3">3/12</div>
    +   <div class="grid-col-9">9/12</div>

    +   <div class="grid-col-4">4/12</div>
    +   <div class="grid-col-4">4/12</div>
    +   <div class="grid-col-4">4/12</div>

    +   <div class="grid-col-12">12/12</div>
    + </div>
    ```
More details are available at [Oro Frontend Stylebook](https://doc.oroinc.com/frontend/storefront/css/frontend-stylebook/).

#### CommerceMenuBundle
* `orocommercemenu/js/app/widgets/menu-traveling-widget` was moved to `orocommercemenu/js/app/views/menu-traveling-view`; Now extends `BaseView` instead of `AbstractWidget`
* Updated block type `menu`:
  - Parent layout was changed from `oro_layout.block_type.abstract_configurable` to `oro_layout.block_type.abstract_configurable_container`
  - Added new option:
    + `customItemBlock`: **{default: null}** - Provides the ability to use a custom menu item template.

### Removed

#### CommerceMenuBundle
* Removed `\Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate::getExtras`, its purpose is moved to `\Oro\Bundle\CommerceMenuBundle\MenuUpdate\Propagator\ToMenuItem\ExtrasPropagator`.

#### FrontendBundle
* `orofrontend/default/js/app/views/input-widget/checkbox` was removed; use pure CSS checkbox customization instead.
* `orofrontend/default/js/app/views/input-widget/checkbox-radio` was removed; use pure CSS radio button customization instead.

## 5.0.0 (2022-01-26)
[Show detailed list of changes](incompatibilities-5-0.md)


### Changed

#### CustomerBundle

* The configuration parameter `cookie_secure` has been moved to `visitor_session` node.
* The configuration parameter `cookie_httponly` has been moved to `visitor_session` node.
* The configuration parameter `cookie_samesite` has been moved to `visitor_session` node.
* Updated search configuration file `Oro/Bundle/CustomerBundle/Resources/config/oro/search.yml`:
    * added configuration for next field `oro_customer_id`
    
### Removed

#### FrontendBundle
* `orofrontend/default/js/widgets/line-clamp-widget` was removed; use CSS `-webkit-line-clamp` property instead. Also was added the `line-clamp` class to does this out of the box.
* `orofrontend/default/js/app/views/footer-align-view` was removed; use CSS features (Flex Box, Grid) to align the required part of HTML.


## 4.2.0 (2020-01-29)
[Show detailed list of changes](incompatibilities-4-2.md)

### Removed

#### FrontendBundle
* The service `oro_frontend.api.rest.routing_options_resolver.remove_single_item_routes` were removed.
  Exclude the `get` action in `Resources/config/oro/api_frontend.yml` instead.


## 4.1.0 (2020-01-31)
[Show detailed list of changes](incompatibilities-4-1.md)

### Changed

#### CustomerBundle
* The constant `ACCOUNT` in `Oro\Bundle\CustomerBundle\Provider\ScopeCustomerCriteriaProvider`
  was replaced with `CUSTOMER`.
* The constant `FIELD_NAME` in `Oro\Bundle\CustomerBundle\Provider\ScopeCustomerGroupCriteriaProvider`
  was replaced with `CUSTOMER_GROUP`.

#### FrontendBundle
* A validation of `web_backend_prefix` container parameter was added.
  The parameter value must not be null and must start with a slash and not end with a slash.
* The method `isFrontendRequest(Request $request = null): bool` of `Oro\Bundle\FrontendBundle\Request\FrontendHelper`
  was changed to `isFrontendRequest(): bool`. To check whether a request is a storefront request use
  `isFrontendUrl($request->getPathInfo())`.
* The class `Oro\Bundle\FrontendBundle\Provider\ActionCurrentApplicationProvider`
  was renamed to `Oro\Bundle\FrontendBundle\Provider\FrontendCurrentApplicationProvider`.

### Removed
* `*.class` parameters for all entities were removed from the dependency injection container.
The entity class names should be used directly, e.g. `'Oro\Bundle\EmailBundle\Entity\Email'`
instead of `'%oro_email.email.entity.class%'` (in service definitions, datagrid config files, placeholders, etc.), and
`\Oro\Bundle\EmailBundle\Entity\Email::class` instead of `$container->getParameter('oro_email.email.entity.class')`
(in PHP code).



## 4.0.0 (2019-07-31)
[Show detailed list of changes](incompatibilities-4-0.md)


### Changed
#### FrontendBundle
* The format of `Resources/views/layouts/{folder}/config/screens.yml` was changed. The `screens` root node was removed. It was done to make format of this file consistent with other config files, such as `Resources/views/layouts/{folder}/config/assets.yml`, `Resources/views/layouts/{folder}/config/images.yml` and `Resources/views/layouts/{folder}/config/page_templates.yml`.

* In `Oro\Bundle\FrontendBundle\Controller\Api\Rest\WorkflowController::startAction` 
 (`/api/rest/{version}/workflow/start/{workflowName}/{transitionName}` path)
 action the request method was changed to POST. 
* In `Oro\Bundle\FrontendBundle\Controller\Api\Rest\WorkflowController::transitAction` 
 (`/api/rest/{version}/workflow/transit/{workflowItemId}/{transitionName}` path)
 action the request method was changed to POST.

## 3.1.4 

### Removed
#### CommerceMenuBundle
* Service `oro_commerce_menu.namespace_migration_provider`

#### FrontendBundle
* Services `oro_frontend.namespace_migration_provider`, `oro_frontend.namespace_migration_fix_product_provider` and the logic that used them were removed.


## 3.1.3 (2019-02-19)

## 3.1.2 (2019-02-05)

## 3.1.0 (2019-01-30)
[Show detailed list of changes](incompatibilities-3-1.md)

### Added
#### CustomerBundle
* A new email template `customer_user_welcome_email_registered_by_admin` was added. It is sent when the administrator or a customer user manager creates a new customer user with the "Send Welcome Email" option selected or when the administrator confirms a customer user from the management console.


### Changed
#### CustomerBundle
* The field `username` was removed from `/admin/api/customerusers` REST API resource.
* The `frontend_owner_type` entity configuration attribute for Customer entity is set to `FRONTEND_CUSTOMER`.
  The `Full` access level was changed to `Сorporate (All Levels)` for Customer entity for `ROLE_FRONTEND_ADMINISTRATOR` customer user role.
  For all other customer user roles the `Full` access level was changed to `Department (Same Level)`.
  
* Changes in `/admin/api/customer_user_addresses` REST API resource:
    - the resource name was changed to `/admin/api/customeruseraddresses`
    - the attribute `created` was renamed to `createdAt`
    - the attribute `updated` was renamed to `updatedAt`
    - the relationship `frontendOwner` was renamed to `customerUser`
* The name for `/admin/api/customer_users` REST API resource was changed to `/admin/api/customerusers`.
* The name for `/admin/api/customer_user_roles` REST API resource was changed to `/admin/api/customeruserroles`.
* The name for `/admin/api/customer_groups` REST API resource was changed to `/admin/api/customergroups`.
* The name for `/admin/api/customer_rating` REST API resource was changed to `/admin/api/customerratings`.
* Removed the `oro_customer.send_password_in_welcome_email` config option to prevent issues with security when a plain password is sent by email.
* There is no password provided while rendering the `customer_user_welcome_email` email template. Please update your customization of this email template. It is recommended to use a reset password link in the email template instead of a plain password.

## 3.0.0 (2018-07-27)

[Show detailed list of changes](incompatibilities-3-0.md)

### Changed
#### CustomerBundle
* The `CustomerGroup::customers` relation was removed as well as other related logic.



## 2.6.0 (2018-01-31)

### Changed
#### FrontendBundle
* Added `frontend` option for datagrids. This option should be set to `true` for all datagrids are used in the store frontend. For details see [Frontend Datagrids](https://doc.oroinc.com/bundles/commerce/FrontendBundle/frontend-access/#frontend-datagrids)

## 2.5.0 (2017-11-30)

[Show detailed list of changes](incompatibilities-2-5.md)

### Added
#### ACL
* In case when Customer Portal is installed together with CRM, the `Account Manager` role has full permissions for Account and Contact entities. The permissions for the `Account Manager` is not changed if Customer Portal is added to already installed CRM.

## 2.4.0 (2017-09-29)
[Show detailed list of changes](incompatibilities-2-4.md)

### Changed
#### FrontendBundle
* some inline underscore templates were moved to separate .html file for each template.

### Removed
#### CustomerBundle
* removed interface `ConstraintByValidationGroups`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/2.4.0/src/Oro/Bundle/OrderBundle/Validator/Constraints/ConstraintByValidationGroups.php "Oro\Bundle\OrderBundle\Validator\Constraints\ConstraintByValidationGroups")</sup>.
* removed constraint class `OrderAddress`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/2.4.0/src/Oro/Bundle/OrderBundle/Validator/Constraints/OrderAddress.php "Oro\Bundle\OrderBundle\Validator\Constraints\OrderAddress")</sup>.
* removed constraint validator `OrderAddressValidator`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/2.4.0/src/Oro/Bundle/OrderBundle/Validator/Constraints/OrderAddressValidator.php "Oro\Bundle\OrderBundle\Validator\Constraints\OrderAddressValidator")</sup>.

## 2.3.0 (2017-07-28)
[Show detailed list of changes](incompatibilities-2-3.md)

### Changed
#### CustomerBundle
* the DI container parameter `oro_customer.entity.owners` was changed
    - the option `local_level` was renamed to `business_unit`
    - the option `basic_level` was renamed to `user`
    
### Removed
#### CustomerBundle
* class `RecordOwnerDataListener`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/2.3.0/src/Oro/Bundle/CustomerBundle/EventListener/RecordOwnerDataListener.php "Oro\Bundle\CustomerBundle\EventListener\RecordOwnerDataListener")</sup>
    - constant `OWNER_TYPE_ACCOUNT` was removed, use `OWNER_TYPE_CUSTOMER` instead
    
## 2.2.0 (2017-05-31)
[Show detailed list of changes](incompatibilities-2-2.md)
