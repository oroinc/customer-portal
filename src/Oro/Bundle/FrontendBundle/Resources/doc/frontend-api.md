# Frontend REST API

The REST API resources for the store frontend is accessible via `http://<hostname>/api/`,
while REST API resources for the admin console is accessible via `http://<hostname>/admin/api/`.
Also there are two REST API sandboxes, one for the store frontend (`http://<hostname>/api/doc`) and another
for the admin console (`http://<hostname>/admin/api/doc`).

All approaches described in [ApiBundle](../../../../../../../platform/src/Oro/Bundle/ApiBundle/README.md) are applicable
to REST API resources for the store frontend, but there are several differences:

- for configuration files use `Resources/config/oro/api_frontend.yml`, not `Resources/config/oro/api.yml`
- for documentation files use `Resources/doc/api_frontend` folder, not `Resources/doc/api`
- for API processors use `frontend` request type
- for API routes use `frontend_rest_api` group instead of `rest_api`, and set `frontend` option to `true`
- for API functional tests use `Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase` instead of `Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase`. By default all API requests are executed by anonymous user. To execute them by the customer user with administrative permissions you can use `Oro\Bundle\CustomerBundle\Tests\Functional\Api\DataFixtures\LoadFrontendApiCustomerUserData` data fixture, just add `$this->loadFixtures([LoadFrontendApiCustomerUserData::class]);` in `setUp()` method of your test class.
