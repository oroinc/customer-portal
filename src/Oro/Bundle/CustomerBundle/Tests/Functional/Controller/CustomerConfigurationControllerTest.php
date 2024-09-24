<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomers;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Test\Functional\RolePermissionExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CustomerConfigurationControllerTest extends WebTestCase
{
    use RolePermissionExtension;

    private Customer $customer;

    #[\Override]
    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());

        $this->loadFixtures([LoadCustomers::class]);

        $this->customer = $this->getReference('customer.level_1');
    }

    public function testThatAccessIsDeniedWhenEditPermissionIsNotSet()
    {
        $this->updateRolePermission(
            'ROLE_ADMINISTRATOR',
            Customer::class,
            AccessLevel::NONE_LEVEL,
            'EDIT'
        );

        $this->client->request('GET', $this->getUrl('oro_customer_config', [
            'id' => $this->customer->getId()
        ]));

        self::assertHtmlResponseStatusCodeEquals($this->client->getResponse(), 403);
    }
}
