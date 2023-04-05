<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller;

use Oro\Bundle\ConfigBundle\Tests\Functional\Controller\AbstractConfigurationControllerTest;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomer;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Test\Functional\RolePermissionExtension;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;

class CustomerConfigurationControllerTest extends AbstractConfigurationControllerTest
{
    use RolePermissionExtension;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([LoadOrganization::class, LoadCustomer::class]);
    }

    public function testThatAccessIsDeniedWhenEditPermissionIsNotSet()
    {
        $customer = $this->getReference(LoadCustomer::CUSTOMER);

        $this->updateRolePermission(
            'ROLE_ADMINISTRATOR',
            Customer::class,
            AccessLevel::NONE_LEVEL,
            'EDIT'
        );

        $this->client->request('GET', $this->getUrl('oro_customer_config', [
            'id' => $customer->getId()
        ]));

        self::assertHtmlResponseStatusCodeEquals($this->client->getResponse(), 403);

        $this->updateRolePermission(
            'ROLE_ADMINISTRATOR',
            Customer::class,
            AccessLevel::GLOBAL_LEVEL,
            'EDIT'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequestUrl(array $parameters): string
    {
        $customer = $this->getReference(LoadCustomer::CUSTOMER);
        $parameters['id'] = $customer->getId();

        return $this->getUrl('oro_customer_config', $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters(): array
    {
        return [
            'main user configuration page' => [
                'parameters' => [
                    'activeGroup' => null,
                    'activeSubGroup' => null,
                ],
                'expected' => [
                    'Product Data Export',
                    'Enable Product Grid Export',
                ]
            ],
            'user configuration sub page' => [
                'parameters' => [
                    'activeGroup' => 'commerce',
                    'activeSubGroup' => 'customer_settings',
                ],
                'expected' => [
                    'Product Data Export',
                    'Enable Product Grid Export',
                ]
            ],
        ];
    }
}
