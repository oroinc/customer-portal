<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\RestJsonApi;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\DataFixtures\LoadBuyerCustomerUserData;
use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Test\Functional\RolePermissionExtension;
use Symfony\Component\HttpFoundation\Response;

class CustomerUserAddressPermissionsTest extends FrontendRestJsonApiTestCase
{
    use RolePermissionExtension;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
            LoadBuyerCustomerUserData::class,
            '@OroCustomerBundle/Tests/Functional/Api/Frontend/DataFixtures/customer_user_address.yml'
        ]);
    }

    public function testUpdateOwnAddressOnBasicAccessLevel()
    {
        $this->updateRolePermissions(
            'ROLE_FRONTEND_BUYER',
            CustomerUserAddress::class,
            [
                'VIEW'   => AccessLevel::DEEP_LEVEL,
                'EDIT'   => AccessLevel::BASIC_LEVEL,
                'ASSIGN' => AccessLevel::DEEP_LEVEL,
                'CREATE' => AccessLevel::LOCAL_LEVEL
            ]
        );

        $addressId = $this->getReference('customer_user_address')->getId();
        $data = [
            'data' => [
                'type'       => 'customeruseraddresses',
                'id'         => (string)$addressId,
                'attributes' => [
                    'label' => 'Updated Address'
                ]
            ]
        ];

        $response = $this->patch(
            ['entity' => 'customeruseraddresses', 'id' => (string)$addressId],
            $data
        );

        $this->assertResponseContains($data, $response);

        /** @var CustomerUserAddress $address */
        $address = $this->getEntityManager()->find(CustomerUserAddress::class, $addressId);
        self::assertNotNull($address);
        self::assertEquals('Updated Address', $address->getLabel());
    }

    public function testTryToUpdateNotOwnAddressOnBasicAccessLevel()
    {
        $this->updateRolePermissions(
            'ROLE_FRONTEND_BUYER',
            CustomerUserAddress::class,
            [
                'VIEW'   => AccessLevel::DEEP_LEVEL,
                'EDIT'   => AccessLevel::BASIC_LEVEL,
                'ASSIGN' => AccessLevel::DEEP_LEVEL,
                'CREATE' => AccessLevel::LOCAL_LEVEL
            ]
        );

        $addressId = $this->getReference('another_customer_user_address2')->getId();
        $data = [
            'data' => [
                'type'       => 'customeruseraddresses',
                'id'         => (string)$addressId,
                'attributes' => [
                    'label' => 'Updated Address'
                ]
            ]
        ];

        $response = $this->patch(
            ['entity' => 'customeruseraddresses', 'id' => (string)$addressId],
            $data,
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'access denied exception',
                'detail' => 'No access by "EDIT" permission to the entity.'
            ],
            $response,
            Response::HTTP_FORBIDDEN
        );
    }

    public function testDeleteOwnAddressOnBasicAccessLevel()
    {
        $this->updateRolePermissions(
            'ROLE_FRONTEND_BUYER',
            CustomerUserAddress::class,
            [
                'VIEW'   => AccessLevel::DEEP_LEVEL,
                'CREATE' => AccessLevel::LOCAL_LEVEL,
                'EDIT'   => AccessLevel::LOCAL_LEVEL,
                'DELETE' => AccessLevel::BASIC_LEVEL,
                'ASSIGN' => AccessLevel::DEEP_LEVEL
            ]
        );

        $addressId = $this->getReference('customer_user_address')->getId();

        $this->delete(
            ['entity' => 'customeruseraddresses', 'id' => (string)$addressId]
        );

        $address = $this->getEntityManager()
            ->find(CustomerUserAddress::class, $addressId);
        self::assertTrue(null === $address);
    }

    public function testTryToDeleteNotOwnAddressOnBasicAccessLevel()
    {
        $this->updateRolePermissions(
            'ROLE_FRONTEND_BUYER',
            CustomerUserAddress::class,
            [
                'VIEW'   => AccessLevel::DEEP_LEVEL,
                'CREATE' => AccessLevel::LOCAL_LEVEL,
                'EDIT'   => AccessLevel::LOCAL_LEVEL,
                'DELETE' => AccessLevel::BASIC_LEVEL,
                'ASSIGN' => AccessLevel::DEEP_LEVEL
            ]
        );

        $addressId = $this->getReference('another_customer_user_address2')->getId();
        $response = $this->delete(
            ['entity' => 'customeruseraddresses', 'id' => $addressId],
            [],
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'access denied exception',
                'detail' => 'No access by "DELETE" permission to the entity.'
            ],
            $response,
            Response::HTTP_FORBIDDEN
        );
    }
}
