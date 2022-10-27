<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller\Frontend\Api\Rest;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\DataFixtures\LoadAdminCustomerUserData;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Test\Functional\RolePermissionExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CustomerAddressControllerApiTest extends WebTestCase
{
    use RolePermissionExtension;

    protected function setUp(): void
    {
        $this->initClient();
        $this->client->useHashNavigation(true);
        $this->loadFixtures(
            [
                LoadAdminCustomerUserData::class,
                '@OroCustomerBundle/Tests/Functional/Api/Frontend/DataFixtures/customer_address.yml',
            ]
        );

        parent::setUp();
    }

    public function testGetAddressesForOwnCustomerOnCorporateAceessLevel()
    {
        $role = $this->getReference('admin')->getRole();
        $this->updateRolePermission($role, Customer::class, AccessLevel::DEEP_LEVEL);
        $this->updateRolePermission($role, CustomerAddress::class, AccessLevel::DEEP_LEVEL);
        $this->loginCustomerUser();

        $customer = $this->getReference('customer');
        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_customer_frontend_get_customer_addresses', ['entityId' => $customer->getId()])
        );

        $result = self::getJsonResponseContent($this->client->getResponse(), 200);
        self::assertCount(3, $result);
        $this->assertResponseDataHaveLabels(
            ['Address 3', 'Address 2', 'Address 1'],
            $result
        );
    }

    public function testGetAddressesForChildCustomerOnCorporateAceessLevel()
    {
        $role = $this->getReference('admin')->getRole();
        $this->updateRolePermission($role, Customer::class, AccessLevel::DEEP_LEVEL);
        $this->updateRolePermission($role, CustomerAddress::class, AccessLevel::DEEP_LEVEL);
        $this->loginCustomerUser();

        $customer = $this->getReference('customer1');
        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_customer_frontend_get_customer_addresses', ['entityId' => $customer->getId()])
        );

        $result = self::getJsonResponseContent($this->client->getResponse(), 200);
        self::assertCount(1, $result);
        $this->assertResponseDataHaveLabels(
            ['Address 3'],
            $result
        );
    }

    public function testTryToGetAddressesForAnotherCustomerOnCorporateAceessLevel()
    {
        $role = $this->getReference('admin')->getRole();
        $this->updateRolePermission($role, Customer::class, AccessLevel::DEEP_LEVEL);
        $this->updateRolePermission($role, CustomerAddress::class, AccessLevel::DEEP_LEVEL);
        $this->loginCustomerUser();

        $customer = $this->getReference('another_customer');
        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_customer_frontend_get_customer_addresses', ['entityId' => $customer->getId()])
        );

        $result = self::getJsonResponseContent($this->client->getResponse(), 403);
        self::assertEquals(['code' => 403], $result);
    }

    public function testGetAddressesForOwnCustomerOnDepartmentAceessLevel()
    {
        $role = $this->getReference('admin')->getRole();
        $this->updateRolePermission($role, Customer::class, AccessLevel::LOCAL_LEVEL);
        $this->updateRolePermission($role, CustomerAddress::class, AccessLevel::LOCAL_LEVEL);
        $this->loginCustomerUser();

        $customer = $this->getReference('customer');
        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_customer_frontend_get_customer_addresses', ['entityId' => $customer->getId()])
        );

        $result = self::getJsonResponseContent($this->client->getResponse(), 200);
        self::assertCount(2, $result);
        $this->assertResponseDataHaveLabels(
            ['Address 2', 'Address 1'],
            $result
        );
    }

    public function testTryToGetAddressesForChildCustomerOnDepartmentAceessLevel()
    {
        $role = $this->getReference('admin')->getRole();
        $this->updateRolePermission($role, Customer::class, AccessLevel::LOCAL_LEVEL);
        $this->updateRolePermission($role, CustomerAddress::class, AccessLevel::LOCAL_LEVEL);
        $this->loginCustomerUser();

        $customer = $this->getReference('customer1');
        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_customer_frontend_get_customer_addresses', ['entityId' => $customer->getId()])
        );

        $result = self::getJsonResponseContent($this->client->getResponse(), 403);
        self::assertEquals(['code' => 403], $result);
    }

    public function testTryToGetAddressesForAnotherCustomerOnDepartmentAceessLevel()
    {
        $role = $this->getReference('admin')->getRole();
        $this->updateRolePermission($role, Customer::class, AccessLevel::LOCAL_LEVEL);
        $this->updateRolePermission($role, CustomerAddress::class, AccessLevel::LOCAL_LEVEL);
        $this->loginCustomerUser();

        $customer = $this->getReference('another_customer');
        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_customer_frontend_get_customer_addresses', ['entityId' => $customer->getId()])
        );

        $result = self::getJsonResponseContent($this->client->getResponse(), 403);
        self::assertEquals(['code' => 403], $result);
    }

    public function testTryToGetAddressesForOwnCustomerOnNoneAceessLevel()
    {
        $role = $this->getReference('admin')->getRole();
        $this->updateRolePermission($role, Customer::class, AccessLevel::NONE_LEVEL);
        $this->updateRolePermission($role, CustomerAddress::class, AccessLevel::NONE_LEVEL);
        $this->loginCustomerUser();

        $customer = $this->getReference('customer');
        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_customer_frontend_get_customer_addresses', ['entityId' => $customer->getId()])
        );

        $result = self::getJsonResponseContent($this->client->getResponse(), 403);
        self::assertEquals(['code' => 403], $result);
    }

    public function testTryToGetAddressesForChildCustomerOnNoneAceessLevel()
    {
        $role = $this->getReference('admin')->getRole();
        $this->updateRolePermission($role, Customer::class, AccessLevel::NONE_LEVEL);
        $this->updateRolePermission($role, CustomerAddress::class, AccessLevel::NONE_LEVEL);
        $this->loginCustomerUser();

        $customer = $this->getReference('customer1');
        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_customer_frontend_get_customer_addresses', ['entityId' => $customer->getId()])
        );

        $result = self::getJsonResponseContent($this->client->getResponse(), 403);
        self::assertEquals(['code' => 403], $result);
    }

    public function testTryToGetAddressesForAnotherCustomerOnNoneAceessLevel()
    {
        $role = $this->getReference('admin')->getRole();
        $this->updateRolePermission($role, Customer::class, AccessLevel::NONE_LEVEL);
        $this->updateRolePermission($role, CustomerAddress::class, AccessLevel::NONE_LEVEL);
        $this->loginCustomerUser();

        $customer = $this->getReference('another_customer');
        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_customer_frontend_get_customer_addresses', ['entityId' => $customer->getId()])
        );

        $result = self::getJsonResponseContent($this->client->getResponse(), 403);
        self::assertEquals(['code' => 403], $result);
    }

    public function testGetAddressForOwnCustomerOnCorporateAceessLevel()
    {
        $role = $this->getReference('admin')->getRole();
        $this->updateRolePermission($role, Customer::class, AccessLevel::DEEP_LEVEL);
        $this->updateRolePermission($role, CustomerAddress::class, AccessLevel::DEEP_LEVEL);
        $this->loginCustomerUser();

        $customer = $this->getReference('customer');
        $address = $this->getReference('customer_address1');
        $this->client->jsonRequest(
            'GET',
            $this->getUrl(
                'oro_api_customer_frontend_get_customer_address',
                [
                    'entityId'  => $customer->getId(),
                    'addressId' => $address->getId(),
                ]
            )
        );

        $result = self::getJsonResponseContent($this->client->getResponse(), 200);
        self::assertEquals('Address 1', $result['label']);
    }

    public function testGetAddressForChildCustomerOnCorporateAceessLevel()
    {
        $role = $this->getReference('admin')->getRole();
        $this->updateRolePermission($role, Customer::class, AccessLevel::DEEP_LEVEL);
        $this->updateRolePermission($role, CustomerAddress::class, AccessLevel::DEEP_LEVEL);
        $this->loginCustomerUser();

        $customer = $this->getReference('customer1');
        $address = $this->getReference('customer_address3');
        $this->client->jsonRequest(
            'GET',
            $this->getUrl(
                'oro_api_customer_frontend_get_customer_address',
                [
                    'entityId'  => $customer->getId(),
                    'addressId' => $address->getId(),
                ]
            )
        );

        $result = self::getJsonResponseContent($this->client->getResponse(), 200);
        self::assertEquals('Address 3', $result['label']);
    }

    public function testTryToGetAddressForAnotherCustomerOnCorporateAceessLevel()
    {
        $role = $this->getReference('admin')->getRole();
        $this->updateRolePermission($role, Customer::class, AccessLevel::DEEP_LEVEL);
        $this->updateRolePermission($role, CustomerAddress::class, AccessLevel::DEEP_LEVEL);
        $this->loginCustomerUser();

        $customer = $this->getReference('another_customer');
        $address = $this->getReference('another_customer_address1');
        $this->client->jsonRequest(
            'GET',
            $this->getUrl(
                'oro_api_customer_frontend_get_customer_address',
                [
                    'entityId'  => $customer->getId(),
                    'addressId' => $address->getId(),
                ]
            )
        );

        $result = self::getJsonResponseContent($this->client->getResponse(), 403);
        self::assertEquals(['code' => 403], $result);
    }

    public function testGetAddressForOwnCustomerOnDepartmentAceessLevel()
    {
        $role = $this->getReference('admin')->getRole();
        $this->updateRolePermission($role, Customer::class, AccessLevel::LOCAL_LEVEL);
        $this->updateRolePermission($role, CustomerAddress::class, AccessLevel::LOCAL_LEVEL);
        $this->loginCustomerUser();

        $customer = $this->getReference('customer');
        $address = $this->getReference('customer_address1');
        $this->client->jsonRequest(
            'GET',
            $this->getUrl(
                'oro_api_customer_frontend_get_customer_address',
                [
                    'entityId'  => $customer->getId(),
                    'addressId' => $address->getId(),
                ]
            )
        );

        $result = self::getJsonResponseContent($this->client->getResponse(), 200);
        self::assertEquals('Address 1', $result['label']);
    }

    public function testTryToGetAddressForChildCustomerOnDepartmentAceessLevel()
    {
        $role = $this->getReference('admin')->getRole();
        $this->updateRolePermission($role, Customer::class, AccessLevel::LOCAL_LEVEL);
        $this->updateRolePermission($role, CustomerAddress::class, AccessLevel::LOCAL_LEVEL);
        $this->loginCustomerUser();

        $customer = $this->getReference('customer1');
        $address = $this->getReference('customer_address3');
        $this->client->jsonRequest(
            'GET',
            $this->getUrl(
                'oro_api_customer_frontend_get_customer_address',
                [
                    'entityId'  => $customer->getId(),
                    'addressId' => $address->getId(),
                ]
            )
        );

        $result = self::getJsonResponseContent($this->client->getResponse(), 403);
        self::assertEquals(['code' => 403], $result);
    }

    public function testTryToGetAddressForAnotherCustomerOnDepartmentAceessLevel()
    {
        $role = $this->getReference('admin')->getRole();
        $this->updateRolePermission($role, Customer::class, AccessLevel::LOCAL_LEVEL);
        $this->updateRolePermission($role, CustomerAddress::class, AccessLevel::LOCAL_LEVEL);
        $this->loginCustomerUser();

        $customer = $this->getReference('another_customer');
        $address = $this->getReference('another_customer_address1');
        $this->client->jsonRequest(
            'GET',
            $this->getUrl(
                'oro_api_customer_frontend_get_customer_address',
                [
                    'entityId'  => $customer->getId(),
                    'addressId' => $address->getId(),
                ]
            )
        );

        $result = self::getJsonResponseContent($this->client->getResponse(), 403);
        self::assertEquals(['code' => 403], $result);
    }

    public function testTryToGetAddressForOwnCustomerOnNoneAceessLevel()
    {
        $role = $this->getReference('admin')->getRole();
        $this->updateRolePermission($role, Customer::class, AccessLevel::NONE_LEVEL);
        $this->updateRolePermission($role, CustomerAddress::class, AccessLevel::NONE_LEVEL);
        $this->loginCustomerUser();

        $customer = $this->getReference('customer');
        $address = $this->getReference('customer_address1');
        $this->client->jsonRequest(
            'GET',
            $this->getUrl(
                'oro_api_customer_frontend_get_customer_address',
                [
                    'entityId'  => $customer->getId(),
                    'addressId' => $address->getId(),
                ]
            )
        );

        $result = self::getJsonResponseContent($this->client->getResponse(), 403);
        self::assertEquals(['code' => 403], $result);
    }

    public function testTryToGetAddressForChildCustomerOnNoneAceessLevel()
    {
        $role = $this->getReference('admin')->getRole();
        $this->updateRolePermission($role, Customer::class, AccessLevel::NONE_LEVEL);
        $this->updateRolePermission($role, CustomerAddress::class, AccessLevel::NONE_LEVEL);
        $this->loginCustomerUser();

        $customer = $this->getReference('customer1');
        $address = $this->getReference('customer_address3');
        $this->client->jsonRequest(
            'GET',
            $this->getUrl(
                'oro_api_customer_frontend_get_customer_address',
                [
                    'entityId'  => $customer->getId(),
                    'addressId' => $address->getId(),
                ]
            )
        );

        $result = self::getJsonResponseContent($this->client->getResponse(), 403);
        self::assertEquals(['code' => 403], $result);
    }

    public function testTryToGetAddressForAnotherCustomerOnNoneAceessLevel()
    {
        $role = $this->getReference('admin')->getRole();
        $this->updateRolePermission($role, Customer::class, AccessLevel::NONE_LEVEL);
        $this->updateRolePermission($role, CustomerAddress::class, AccessLevel::NONE_LEVEL);
        $this->loginCustomerUser();

        $customer = $this->getReference('another_customer');
        $address = $this->getReference('another_customer_address1');
        $this->client->jsonRequest(
            'GET',
            $this->getUrl(
                'oro_api_customer_frontend_get_customer_address',
                [
                    'entityId'  => $customer->getId(),
                    'addressId' => $address->getId(),
                ]
            )
        );

        $result = self::getJsonResponseContent($this->client->getResponse(), 403);
        self::assertEquals(['code' => 403], $result);
    }

    private function loginCustomerUser()
    {
        self::getClientInstance()->setServerParameters(
            self::generateBasicAuthHeader('frontend_admin_api@example.com', 'test')
        );
    }

    private function assertResponseDataHaveLabels(array $expectedLabels, array $resultData)
    {
        foreach ($resultData as $result) {
            $label = $result['label'];
            self::assertContains(
                $label,
                $expectedLabels,
                sprintf('Non expected label "%s". Expected values: %s', $label, implode(', ', $expectedLabels))
            );
        }
    }
}
