<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller\Frontend\Api\Rest;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\DataFixtures\LoadAdminCustomerUserData;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Test\Functional\RolePermissionExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CustomerUserAddressControllerApiTest extends WebTestCase
{
    use RolePermissionExtension;

    protected function setUp(): void
    {
        $this->initClient();
        $this->client->useHashNavigation(true);
        $this->loadFixtures(
            [
                LoadAdminCustomerUserData::class,
                '@OroCustomerBundle/Tests/Functional/Api/Frontend/DataFixtures/customer_user_address.yml',
            ]
        );

        parent::setUp();
    }

    public function testGetAddressesForOwnCustomerUserOnCorporateAceessLevel()
    {
        $role = $this->getReference('admin')->getRole();
        $this->updateRolePermission($role, CustomerUser::class, AccessLevel::DEEP_LEVEL);
        $this->updateRolePermission($role, CustomerUserAddress::class, AccessLevel::DEEP_LEVEL);
        $this->loginCustomerUser();

        $user = $this->getReference('customer_user');
        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_customer_frontend_get_customeruser_addresses', ['entityId' => $user->getId()])
        );

        $result = self::getJsonResponseContent($this->client->getResponse(), 200);
        self::assertCount(6, $result);
        $this->assertResponseDataHaveLabels(
            ['Another Address 2', 'Another Address 1', 'Address 3', 'Address 2', 'Address 1', 'Address'],
            $result
        );
    }

    public function testGetAddressesForSameCustomerCustomerUserOnCorporateAceessLevel()
    {
        $role = $this->getReference('admin')->getRole();
        $this->updateRolePermission($role, CustomerUser::class, AccessLevel::DEEP_LEVEL);
        $this->updateRolePermission($role, CustomerUserAddress::class, AccessLevel::DEEP_LEVEL);
        $this->loginCustomerUser();

        $user = $this->getReference('customer_user1');
        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_customer_frontend_get_customeruser_addresses', ['entityId' => $user->getId()])
        );

        $result = self::getJsonResponseContent($this->client->getResponse(), 200);
        self::assertCount(3, $result);
        $this->assertResponseDataHaveLabels(
            ['Address 3', 'Address 2', 'Address 1'],
            $result
        );
    }

    public function testTryToGetAddressesForAnotherCustomerCustomerUserOnCorporateAceessLevel()
    {
        $role = $this->getReference('admin')->getRole();
        $this->updateRolePermission($role, CustomerUser::class, AccessLevel::DEEP_LEVEL);
        $this->updateRolePermission($role, CustomerUserAddress::class, AccessLevel::DEEP_LEVEL);
        $this->loginCustomerUser();

        $user = $this->getReference('customer_user_from_another_department');
        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_customer_frontend_get_customeruser_addresses', ['entityId' => $user->getId()])
        );

        $result = self::getJsonResponseContent($this->client->getResponse(), 403);
        self::assertEquals(['code' => 403], $result);
    }

    public function testGetAddressesForOwnCustomerUserOnDepartmentAceessLevel()
    {
        $role = $this->getReference('admin')->getRole();
        $this->updateRolePermission($role, CustomerUser::class, AccessLevel::LOCAL_LEVEL);
        $this->updateRolePermission($role, CustomerUserAddress::class, AccessLevel::LOCAL_LEVEL);
        $this->loginCustomerUser();

        $user = $this->getReference('customer_user');
        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_customer_frontend_get_customeruser_addresses', ['entityId' => $user->getId()])
        );

        $result = self::getJsonResponseContent($this->client->getResponse(), 200);
        self::assertCount(6, $result);
        $this->assertResponseDataHaveLabels(
            ['Another Address 2', 'Another Address 1', 'Address 3', 'Address 2', 'Address 1', 'Address'],
            $result
        );
    }

    public function testGetAddressesForSameCustomerCustomerUserOnDepartmentAceessLevel()
    {
        $role = $this->getReference('admin')->getRole();
        $this->updateRolePermission($role, CustomerUser::class, AccessLevel::LOCAL_LEVEL);
        $this->updateRolePermission($role, CustomerUserAddress::class, AccessLevel::LOCAL_LEVEL);
        $this->loginCustomerUser();

        $user = $this->getReference('customer_user1');
        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_customer_frontend_get_customeruser_addresses', ['entityId' => $user->getId()])
        );

        $result = self::getJsonResponseContent($this->client->getResponse(), 200);
        self::assertCount(3, $result);
        $this->assertResponseDataHaveLabels(
            ['Address 3', 'Address 2', 'Address 1'],
            $result
        );
    }

    public function testTryToGetAddressesForAnotherCustomerCustomerUserOnDepartmentAceessLevel()
    {
        $role = $this->getReference('admin')->getRole();
        $this->updateRolePermission($role, CustomerUser::class, AccessLevel::LOCAL_LEVEL);
        $this->updateRolePermission($role, CustomerUserAddress::class, AccessLevel::LOCAL_LEVEL);
        $this->loginCustomerUser();

        $user = $this->getReference('customer_user_from_another_department');
        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_customer_frontend_get_customeruser_addresses', ['entityId' => $user->getId()])
        );

        $result = self::getJsonResponseContent($this->client->getResponse(), 403);
        self::assertEquals(['code' => 403], $result);
    }

    public function testGetAddressesForOwnCustomerUserOnUserAceessLevel()
    {
        $role = $this->getReference('admin')->getRole();
        $this->updateRolePermission($role, CustomerUser::class, AccessLevel::LOCAL_LEVEL);
        $this->updateRolePermission($role, CustomerUserAddress::class, AccessLevel::BASIC_LEVEL);
        $this->loginCustomerUser();

        $user = $this->getReference('customer_user');
        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_customer_frontend_get_customeruser_addresses', ['entityId' => $user->getId()])
        );

        $result = self::getJsonResponseContent($this->client->getResponse(), 200);
        self::assertCount(1, $result);
        $this->assertResponseDataHaveLabels(
            ['Address'],
            $result
        );
    }

    public function testGetAddressesForSameCustomerCustomerUserOnUserAceessLevel()
    {
        $role = $this->getReference('admin')->getRole();
        $this->updateRolePermission($role, CustomerUser::class, AccessLevel::LOCAL_LEVEL);
        $this->updateRolePermission($role, CustomerUserAddress::class, AccessLevel::BASIC_LEVEL);
        $this->loginCustomerUser();

        $user = $this->getReference('customer_user1');
        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_customer_frontend_get_customeruser_addresses', ['entityId' => $user->getId()])
        );

        $result = self::getJsonResponseContent($this->client->getResponse(), 200);
        self::assertCount(0, $result);
    }

    public function testGetAddressesForOwnCustomerUserOnNoneAceessLevel()
    {
        $role = $this->getReference('admin')->getRole();
        $this->updateRolePermission($role, CustomerUser::class, AccessLevel::NONE_LEVEL);
        $this->updateRolePermission($role, CustomerUserAddress::class, AccessLevel::NONE_LEVEL);
        $this->loginCustomerUser();

        $user = $this->getReference('customer_user');
        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_customer_frontend_get_customeruser_addresses', ['entityId' => $user->getId()])
        );

        $result = self::getJsonResponseContent($this->client->getResponse(), 403);
        self::assertEquals(['code' => 403], $result);
    }

    public function testTryToGetAddressesForSameCustomerCustomerUserOnNoneAceessLevel()
    {
        $role = $this->getReference('admin')->getRole();
        $this->updateRolePermission($role, CustomerUser::class, AccessLevel::NONE_LEVEL);
        $this->updateRolePermission($role, CustomerUserAddress::class, AccessLevel::NONE_LEVEL);
        $this->loginCustomerUser();

        $user = $this->getReference('customer_user1');
        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_customer_frontend_get_customeruser_addresses', ['entityId' => $user->getId()])
        );

        $result = self::getJsonResponseContent($this->client->getResponse(), 403);
        self::assertEquals(['code' => 403], $result);
    }

    public function testTryToGetAddressesForAnotherCustomerCustomerUserOnNoneAceessLevel()
    {
        $role = $this->getReference('admin')->getRole();
        $this->updateRolePermission($role, CustomerUser::class, AccessLevel::NONE_LEVEL);
        $this->updateRolePermission($role, CustomerUserAddress::class, AccessLevel::NONE_LEVEL);
        $this->loginCustomerUser();

        $user = $this->getReference('customer_user_from_another_department');
        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_customer_frontend_get_customeruser_addresses', ['entityId' => $user->getId()])
        );

        $result = self::getJsonResponseContent($this->client->getResponse(), 403);
        self::assertEquals(['code' => 403], $result);
    }

    public function testGetAddressForOwnCustomerUserOnCorporateAceessLevel()
    {
        $role = $this->getReference('admin')->getRole();
        $this->updateRolePermission($role, CustomerUser::class, AccessLevel::DEEP_LEVEL);
        $this->updateRolePermission($role, CustomerUserAddress::class, AccessLevel::DEEP_LEVEL);
        $this->loginCustomerUser();

        $user = $this->getReference('customer_user');
        $address = $this->getReference('customer_user_address');
        $this->client->jsonRequest(
            'GET',
            $this->getUrl(
                'oro_api_customer_frontend_get_customeruser_address',
                [
                    'entityId'  => $user->getId(),
                    'addressId' => $address->getId(),
                ]
            )
        );

        $result = self::getJsonResponseContent($this->client->getResponse(), 200);
        self::assertEquals('Address', $result['label']);
    }

    public function testGetAddressForSameCustomerCustomerUserOnCorporateAceessLevel()
    {
        $role = $this->getReference('admin')->getRole();
        $this->updateRolePermission($role, CustomerUser::class, AccessLevel::DEEP_LEVEL);
        $this->updateRolePermission($role, CustomerUserAddress::class, AccessLevel::DEEP_LEVEL);
        $this->loginCustomerUser();

        $user = $this->getReference('customer_user1');
        $address = $this->getReference('customer_user_address1');
        $this->client->jsonRequest(
            'GET',
            $this->getUrl(
                'oro_api_customer_frontend_get_customeruser_address',
                [
                    'entityId'  => $user->getId(),
                    'addressId' => $address->getId(),
                ]
            )
        );

        $result = self::getJsonResponseContent($this->client->getResponse(), 200);
        self::assertEquals('Address 1', $result['label']);
    }

    public function testGetAddressForAnotherCustomeCustomerUserOnCorporateAceessLevel()
    {
        $role = $this->getReference('admin')->getRole();
        $this->updateRolePermission($role, CustomerUser::class, AccessLevel::DEEP_LEVEL);
        $this->updateRolePermission($role, CustomerUserAddress::class, AccessLevel::DEEP_LEVEL);
        $this->loginCustomerUser();

        $user = $this->getReference('customer_user_from_another_department');
        $address = $this->getReference('customer_user_address_from_another_department');
        $this->client->jsonRequest(
            'GET',
            $this->getUrl(
                'oro_api_customer_frontend_get_customeruser_address',
                [
                    'entityId'  => $user->getId(),
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
