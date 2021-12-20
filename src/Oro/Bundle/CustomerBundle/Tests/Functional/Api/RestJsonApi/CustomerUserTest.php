<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\DataFixtures\LoadWebsiteData;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

/**
 * @group CommunityEdition
 *
 * @dbIsolationPerTest
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CustomerUserTest extends RestJsonApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
            LoadOrganization::class,
            LoadCustomerUserData::class,
            LoadWebsiteData::class
        ]);
        $role = $this->getEntityManager()
            ->getRepository(CustomerUserRole::class)
            ->findOneBy(['role' => 'ROLE_FRONTEND_ADMINISTRATOR']);
        $this->getReferenceRepository()->addReference('ROLE_FRONTEND_ADMINISTRATOR', $role);
    }

    public function testGetList()
    {
        $response = $this->cget(
            ['entity' => 'customerusers'],
            ['filter[email]' => LoadCustomerUserData::EMAIL]
        );

        $this->assertResponseContains('cget_customer_user.yml', $response);
    }

    public function testGet()
    {
        $customerUserId = $this->getReference(LoadCustomerUserData::EMAIL)->getId();
        $response = $this->get(
            ['entity' => 'customerusers', 'id' => $customerUserId]
        );

        $this->assertResponseContains('get_customer_user.yml', $response);
        $this->assertResponseNotHasAttributes(
            ['password', 'plainPassword', 'salt', 'confirmationToken', 'emailLowercase', 'username'],
            $response
        );
    }

    public function testDelete()
    {
        $customerUserId = $this->getReference(LoadCustomerUserData::EMAIL)->getId();

        $this->delete(
            ['entity' => 'customerusers', 'id' => $customerUserId]
        );

        $deletedCustomerUser = $this->getEntityManager()
            ->find(CustomerUser::class, $customerUserId);
        self::assertTrue(null === $deletedCustomerUser);
    }

    public function testDeleteList()
    {
        $this->cdelete(
            ['entity' => 'customerusers'],
            ['filter[email]' => LoadCustomerUserData::EMAIL]
        );

        $deletedCustomerUser = $this->getEntityManager()
            ->getRepository(CustomerUser::class)
            ->findOneBy(['email' => LoadCustomerUserData::EMAIL]);
        self::assertTrue(null === $deletedCustomerUser);
    }

    public function testCreate()
    {
        $ownerId = $this->getReference('user')->getId();
        $organizationId = $this->getReference('organization')->getId();
        $customerId = $this->getReference('customer.level_1')->getId();

        $data = $this->getRequestData('create_customer_user.yml');
        $response = $this->post(
            ['entity' => 'customerusers'],
            $data
        );

        $customerUserId = (int)$this->getResourceId($response);
        $responseContent = $this->updateResponseContent('create_customer_user.yml', $response);
        $this->assertResponseContains($responseContent, $response);

        /** @var CustomerUser $customerUser */
        $customerUser = $this->getEntityManager()
            ->find(CustomerUser::class, $customerUserId);
        self::assertEquals($data['data']['attributes']['firstName'], $customerUser->getFirstName());
        self::assertEquals($data['data']['attributes']['lastName'], $customerUser->getLastName());
        self::assertEquals($data['data']['attributes']['email'], $customerUser->getEmail());
        self::assertEquals($customerUser->getEmail(), $customerUser->getUsername());
        self::assertEquals($customerId, $customerUser->getCustomer()->getId());
        self::assertEquals($organizationId, $customerUser->getOrganization()->getId());
        self::assertEquals($ownerId, $customerUser->getOwner()->getId());

        self::assertEmpty($customerUser->getPlainPassword());
        self::assertNotEmpty($customerUser->getPassword());
        self::assertNotEmpty($customerUser->getSalt());
        /** @var PasswordEncoderInterface $passwordEncoder */
        $passwordEncoder = self::getContainer()->get('security.encoder_factory')->getEncoder($customerUser);
        self::assertTrue(
            $passwordEncoder->isPasswordValid(
                $customerUser->getPassword(),
                $data['data']['attributes']['password'],
                $customerUser->getSalt()
            )
        );
    }

    public function testCreateWithRequiredDataOnly()
    {
        $ownerId = $this->getReference('user')->getId();
        $organizationId = $this->getReference('organization')->getId();
        $customerId = $this->getReference('customer.level_1')->getId();

        $data = $this->getRequestData('create_customer_user_min.yml');
        $response = $this->post(
            ['entity' => 'customerusers'],
            $data
        );

        $customerUserId = (int)$this->getResourceId($response);
        $responseContent = $data;
        $responseContent['data']['relationships']['customer']['data'] = [
            'type' => 'customers',
            'id'   => (string)$customerId
        ];
        $this->assertResponseContains($responseContent, $response);

        /** @var CustomerUser $customerUser */
        $customerUser = $this->getEntityManager()
            ->find(CustomerUser::class, $customerUserId);
        self::assertEquals($data['data']['attributes']['firstName'], $customerUser->getFirstName());
        self::assertEquals($data['data']['attributes']['lastName'], $customerUser->getLastName());
        self::assertEquals($data['data']['attributes']['email'], $customerUser->getEmail());
        self::assertEquals($customerUser->getEmail(), $customerUser->getUsername());
        self::assertEquals($organizationId, $customerUser->getOrganization()->getId());
        self::assertEquals($ownerId, $customerUser->getOwner()->getId());
        self::assertEquals($customerId, $customerUser->getCustomer()->getId());

        self::assertEmpty($customerUser->getPlainPassword());
        self::assertNotEmpty($customerUser->getPassword());
        self::assertNotEmpty($customerUser->getSalt());
    }

    public function testCreateAsIncludedDataWithRequiredDataOnly()
    {
        $data = [
            'data' => [
                'type'       => 'customers',
                'id'         => 'new_customer',
                'attributes' => [
                    'name' => 'New Customer'
                ]
            ]
        ];
        $userData = $this->getRequestData('create_customer_user_min.yml')['data'];
        $userData['id'] = 'new_user';
        $userData['relationships']['customer']['data']['id'] = 'new_customer';
        $data['included'][] = $userData;

        $response = $this->post(
            ['entity' => 'customers'],
            $data
        );

        $customerId = (int)$this->getResourceId($response);
        /** @var Customer $customer */
        $customer = $this->getEntityManager()
            ->find(Customer::class, $customerId);
        /** @var CustomerUser $customerUser */
        $customerUser = $customer->getUsers()->first();
        self::assertInstanceOf(CustomerUser::class, $customerUser);
        self::assertEmpty($customerUser->getPlainPassword());
        self::assertNotEmpty($customerUser->getPassword());
        self::assertNotEmpty($customerUser->getSalt());
    }

    public function testTryToCreateWithoutData()
    {
        $response = $this->post(
            ['entity' => 'customerusers'],
            ['data' => ['type' => 'customerusers']],
            [],
            false
        );

        $this->assertResponseValidationErrors(
            [
                ['title' => 'not blank constraint', 'source' => ['pointer' => '/data/attributes/email']],
                ['title' => 'not blank constraint', 'source' => ['pointer' => '/data/attributes/firstName']],
                ['title' => 'not blank constraint', 'source' => ['pointer' => '/data/attributes/lastName']],
                ['title' => 'not blank constraint', 'source' => ['pointer' => '/data/relationships/customer/data']]
            ],
            $response
        );
    }

    public function testCreateWithNullPassword()
    {
        $data = $this->getRequestData('create_customer_user_min.yml');
        $data['data']['attributes']['password'] = null;
        $response = $this->post(
            ['entity' => 'customerusers'],
            $data
        );

        /** @var CustomerUser $customerUser */
        $customerUser = $this->getEntityManager()
            ->find(CustomerUser::class, (int)$this->getResourceId($response));

        self::assertEmpty($customerUser->getPlainPassword());
        self::assertNotEmpty($customerUser->getPassword());
        self::assertNotEmpty($customerUser->getSalt());
    }

    public function testCreateWithEmptyPassword()
    {
        $data = $this->getRequestData('create_customer_user_min.yml');
        $data['data']['attributes']['password'] = '';
        $response = $this->post(
            ['entity' => 'customerusers'],
            $data
        );

        /** @var CustomerUser $customerUser */
        $customerUser = $this->getEntityManager()
            ->find(CustomerUser::class, (int)$this->getResourceId($response));

        self::assertEmpty($customerUser->getPlainPassword());
        self::assertNotEmpty($customerUser->getPassword());
        self::assertNotEmpty($customerUser->getSalt());
    }

    public function testTryToCreateWithInvalidPassword()
    {
        $data = $this->getRequestData('create_customer_user_min.yml');
        $data['data']['attributes']['password'] = '1';
        $response = $this->post(
            ['entity' => 'customerusers'],
            $data,
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'password complexity constraint',
                'source' => ['pointer' => '/data/attributes/password']
            ],
            $response
        );
    }

    public function testUpdate()
    {
        $customerUserId = $this->getReference(LoadCustomerUserData::EMAIL)->getId();
        $customerId = $this->getReference('customer.level_1.1')->getId();
        $customerUserRoleId = $this->getEntityManager()
            ->getRepository(CustomerUserRole::class)
            ->findOneBy(['role' => 'ROLE_FRONTEND_BUYER'])
            ->getId();

        $data = [
            'data' => [
                'type'          => 'customerusers',
                'id'            => (string)$customerUserId,
                'attributes'    => [
                    'firstName' => 'Updated First Name'
                ],
                'relationships' => [
                    'customer' => [
                        'data' => ['type' => 'customers', 'id' => (string)$customerId]
                    ],
                    'userRoles'    => [
                        'data' => [
                            ['type' => 'customeruserroles', 'id' => (string)$customerUserRoleId]
                        ]
                    ]
                ]
            ]
        ];
        $this->patch(
            ['entity' => 'customerusers', 'id' => $customerUserId],
            $data
        );

        $customerUser = $this->getEntityManager()
            ->find(CustomerUser::class, $customerUserId);
        self::assertEquals('Updated First Name', $customerUser->getFirstName());
        self::assertEquals($customerId, $customerUser->getCustomer()->getId());
        self::assertTrue($customerUser->hasRole('ROLE_FRONTEND_BUYER'));
        self::assertCount(1, $customerUser->getUserRoles());
    }

    public function testUpdateRelationshipForRoles()
    {
        $customerUserId = $this->getReference(LoadCustomerUserData::EMAIL)->getId();
        $customerUserRoleId = $this->getEntityManager()
            ->getRepository(CustomerUserRole::class)
            ->findOneBy(['role' => 'ROLE_FRONTEND_BUYER'])
            ->getId();

        $this->patchRelationship(
            ['entity' => 'customerusers', 'id' => $customerUserId, 'association' => 'userRoles'],
            [
                'data' => [
                    ['type' => 'customeruserroles', 'id' => (string)$customerUserRoleId]
                ]
            ]
        );

        $customerUser = $this->getEntityManager()
            ->find(CustomerUser::class, $customerUserId);
        self::assertTrue($customerUser->hasRole('ROLE_FRONTEND_BUYER'));
        self::assertCount(1, $customerUser->getUserRoles());
    }

    public function testAddRelationshipForRoles()
    {
        $customerUserId = $this->getReference(LoadCustomerUserData::EMAIL)->getId();
        $customerUserRoleId = $this->getEntityManager()
            ->getRepository(CustomerUserRole::class)
            ->findOneBy(['role' => 'ROLE_FRONTEND_BUYER'])
            ->getId();

        $this->postRelationship(
            ['entity' => 'customerusers', 'id' => $customerUserId, 'association' => 'userRoles'],
            [
                'data' => [
                    ['type' => 'customeruserroles', 'id' => (string)$customerUserRoleId]
                ]
            ]
        );

        $customerUser = $this->getEntityManager()
            ->find(CustomerUser::class, $customerUserId);
        self::assertTrue($customerUser->hasRole('ROLE_FRONTEND_BUYER'));
        self::assertTrue($customerUser->hasRole('ROLE_FRONTEND_ADMINISTRATOR'));
        self::assertCount(2, $customerUser->getUserRoles());
    }

    public function testDeleteRelationshipForRoles()
    {
        $customerUserRoleId = $this->getEntityManager()
            ->getRepository(CustomerUserRole::class)
            ->findOneBy(['role' => 'ROLE_FRONTEND_ADMINISTRATOR'])
            ->getId();
        $customerUser = $this->getReference(LoadCustomerUserData::EMAIL);
        $customerUserId = $customerUser->getId();
        $customerUser->addUserRole(
            $this->getEntityManager()
                ->getRepository(CustomerUserRole::class)
                ->findOneBy(['role' => 'ROLE_FRONTEND_BUYER'])
        );
        $this->getEntityManager()->flush();
        self::assertCount(2, $customerUser->getUserRoles());
        $this->getEntityManager()->clear();

        $this->deleteRelationship(
            ['entity' => 'customerusers', 'id' => $customerUserId, 'association' => 'userRoles'],
            [
                'data' => [
                    ['type' => 'customeruserroles', 'id' => (string)$customerUserRoleId]
                ]
            ]
        );

        $customerUser = $this->getEntityManager()
            ->find(CustomerUser::class, $customerUserId);
        self::assertTrue($customerUser->hasRole('ROLE_FRONTEND_BUYER'));
        self::assertCount(1, $customerUser->getUserRoles());
    }
}
