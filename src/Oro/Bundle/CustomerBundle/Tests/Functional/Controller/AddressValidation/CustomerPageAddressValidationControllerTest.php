<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller\AddressValidation;

use Oro\Bundle\AddressValidationBundle\Test\AddressValidationFeatureAwareTrait;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomer;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerAddresses;
use Oro\Bundle\IntegrationBundle\Tests\Functional\DataFixtures\LoadChannelData;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Test\Functional\RolePermissionExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;

final class CustomerPageAddressValidationControllerTest extends WebTestCase
{
    use RolePermissionExtension;
    use AddressValidationFeatureAwareTrait;

    #[\Override]
    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->loadFixtures([
            LoadCustomer::class,
            LoadCustomerAddresses::class,
            LoadChannelData::class
        ]);
    }

    public function testThatRouteNotFoundWhenFeatureDisabled(): void
    {
        $this->ajaxRequest(
            Request::METHOD_POST,
            $this->getUrl(
                'oro_customer_address_validation_customer',
                [
                    'customer_id' => $this->getReference(LoadCustomer::CUSTOMER)->getId(),
                ]
            )
        );

        self::assertResponseStatusCodeEquals($this->client->getResponse(), 404);
    }

    /**
     * @dataProvider aclProvider
     */
    public function testAddressValidationAclForCreating(string $routeName, int $level, int $statusCode): void
    {
        self::enableAddressValidationFeature(
            $this->getReference('oro_integration:foo_integration')->getId()
        );

        $this->updateRolePermissions(
            User::ROLE_ADMINISTRATOR,
            Customer::class,
            [
                'CREATE' => $level,
            ]
        );

        $this->ajaxRequest(
            Request::METHOD_POST,
            $this->getUrl($routeName, [
                'customer_id' => 0
            ])
        );

        self::assertResponseStatusCodeEquals($this->client->getResponse(), $statusCode);
    }

    /**
     * @dataProvider aclProvider
     */
    public function testThatValidationIsForbiddenForUpdating(string $routeName, int $level, int $statusCode): void
    {
        self::enableAddressValidationFeature(
            $this->getReference('oro_integration:foo_integration')->getId()
        );

        $this->updateRolePermissions(
            User::ROLE_ADMINISTRATOR,
            Customer::class,
            [
                'EDIT' => $level,
            ]
        );

        $this->ajaxRequest(
            Request::METHOD_POST,
            $this->getUrl($routeName, [
                'customer_id' => $this->getReference(LoadCustomer::CUSTOMER)->getId()
            ])
        );

        self::assertResponseStatusCodeEquals($this->client->getResponse(), $statusCode);
    }

    private static function aclProvider(): array
    {
        return [
            ['oro_customer_address_validation_customer', AccessLevel::NONE_LEVEL, 403],
            ['oro_customer_address_validation_customer', AccessLevel::GLOBAL_LEVEL, 200],
        ];
    }
}
