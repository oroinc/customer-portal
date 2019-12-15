# Frontend REST API

The REST API resources for the storefront are accessible via `http://<hostname>/api/`,
while REST API resources for the admin console is accessible via `http://<hostname>/admin/api/`.
There are also two REST API sandboxes, one for the storefront (`http://<hostname>/api/doc`) and other one
for the admin console (`http://<hostname>/admin/api/doc`).

All approaches described in [ApiBundle](../../../../../../../platform/src/Oro/Bundle/ApiBundle/README.md) are applicable
to REST API resources for the storefront but there are several differences:

- for configuration files use `Resources/config/oro/api_frontend.yml`, not `Resources/config/oro/api.yml`
- the default value for `exclusion_policy` option is `custom_fields`, it means that all custom fields
  (fields with `is_extend` = `true` and `owner` = `Custom` in `extend` scope in entity configuration)
  that are not configured explicitly are excluded
- for documentation files use `Resources/doc/api_frontend` folder, not `Resources/doc/api`
- for API processors use `frontend` request type
- for API routes use `Oro\Bundle\FrontendBundle\Controller\FrontendRestApiController` controller
  instead of `Oro\Bundle\ApiBundle\Controller\RestApiController`,
  `frontend_rest_api` group instead of `rest_api`
  and set `frontend` option to `true`
- for [CORS requests configuration](../../../../../../../platform/src/Oro/Bundle/ApiBundle/Resources/doc/cors.md)
  use `oro_frontend / frontend_api / cors` section, not `oro_api / cors`
- for API functional tests use `Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase` instead of
  `Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase`. By default all API requests are executed by
  anonymous user. To execute them by the customer user with administrative permissions you can use
  `Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\DataFixtures\LoadAdminCustomerUserData` data fixture,
  just add `$this->loadFixtures([LoadAdminCustomerUserData::class]);` in `setUp()` method of your test class.
  To execute the test by the customer user with a buyer permissions you can use
  `Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\DataFixtures\LoadBuyerCustomerUserData` data fixture.

Additional notes:

- The [SetWebsite](../../../WebsiteBundle/Api/Processor/SetWebsite.php) processor can be used to assign an entity
  to the current website.
- The [SetCurrency](../../../../../../../platform/src/Oro/Bundle/CurrencyBundle/Api/Processor/SetCurrency.php)
  processor can be used to set the current currency to an entity.
- The [SetCustomer](../../../CustomerBundle/Api/Processor/SetCustomer.php) processor can be used to assign an entity
  to the current customer.
- The [SetCustomerUser](../../../CustomerBundle/Api/Processor/SetCustomerUser.php) processor can be used
  to assign an entity to the current customer user.

An example of registration of such processors:

```yaml
services:
    oro_customer.api.frontend.customer_user.set_website:
        class: Oro\Bundle\WebsiteBundle\Api\Processor\SetWebsite
        arguments:
            - '@oro_api.form_property_accessor'
            - '@oro_website.manager'
        tags:
            - { name: oro.api.processor, action: customize_form_data, requestType: frontend, event: pre_validate, parentAction: create, class: Oro\Bundle\CustomerBundle\Entity\CustomerUser, priority: 20 }

    oro_order.api.set_currency_to_order:
        class: Oro\Bundle\CurrencyBundle\Api\Processor\SetCurrency
        arguments:
            - '@oro_api.form_property_accessor'
            - '@oro_locale.settings'
        tags:
            - { name: oro.api.processor, action: customize_form_data, requestType: frontend, event: pre_validate, parentAction: create, class: Oro\Bundle\OrderBundle\Entity\Order, priority: 15 }

    oro_customer.api.frontend.customer_address.set_customer:
        class: Oro\Bundle\CustomerBundle\Api\Processor\SetCustomer
        arguments:
            - '@oro_api.form_property_accessor'
            - '@oro_security.token_accessor'
            - 'frontendOwner'
        tags:
            - { name: oro.api.processor, action: customize_form_data, requestType: frontend, event: pre_validate, parentAction: create, class: Oro\Bundle\CustomerBundle\Entity\CustomerAddress, priority: 10 }

    oro_customer.api.frontend.customer_user_address.set_customer_user:
        class: Oro\Bundle\CustomerBundle\Api\Processor\SetCustomerUser
        arguments:
            - '@oro_api.form_property_accessor'
            - '@oro_security.token_accessor'
            - 'frontendOwner'
        tags:
            - { name: oro.api.processor, action: customize_form_data, requestType: frontend, event: pre_validate, parentAction: create, class: Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress, priority: 10 }
```
