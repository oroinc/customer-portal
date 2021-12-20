<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\RestJsonApi;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\DataFixtures\LoadBuyerCustomerUserData;
use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Symfony\Component\HttpFoundation\Response;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class CustomerUserForBuyerTest extends FrontendRestJsonApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
            LoadBuyerCustomerUserData::class,
            '@OroCustomerBundle/Tests/Functional/Api/Frontend/DataFixtures/customer_user.yml'
        ]);
    }

    private function disableProfileUpdatePermission(CustomerUser $customerUser): void
    {
        /** @var AclManager $manager */
        $manager = self::getContainer()->get('oro_security.acl.manager');

        foreach ($customerUser->getUserRoles() as $role) {
            $sid = $manager->getSid($role);
            $oid = $manager->getOid('action: oro_customer_frontend_update_own_profile');
            $manager->setPermission($sid, $oid, 0);
            $manager->flush();
        }
    }

    public function testGetListShouldReturnOnlyCurrentLoggedInUser()
    {
        $response = $this->cget(['entity' => 'customerusers']);

        $this->assertResponseContains(
            [
                'data' => [
                    ['type' => 'customerusers', 'id' => '<toString(@customer_user->id)>']
                ]
            ],
            $response
        );
    }

    public function testGetCurrentLoggedInUser()
    {
        $response = $this->get(
            ['entity' => 'customerusers', 'id' => '<toString(@customer_user->id)>']
        );

        $this->assertResponseContains(
            [
                'data' => ['type' => 'customerusers', 'id' => '<toString(@customer_user->id)>']
            ],
            $response
        );
    }

    public function testTryToGetFromChildCustomer()
    {
        $response = $this->get(
            ['entity' => 'customerusers', 'id' => '<toString(@customer_user1->id)>'],
            [],
            [],
            false
        );
        $this->assertResponseValidationError(
            [
                'title'  => 'access denied exception',
                'detail' => 'No access to the entity.'
            ],
            $response,
            Response::HTTP_FORBIDDEN
        );
    }

    public function testTryToGetFromAnotherRootCustomer()
    {
        $response = $this->get(
            ['entity' => 'customerusers', 'id' => '<toString(@another_customer_user->id)>'],
            [],
            [],
            false
        );
        $this->assertResponseValidationError(
            [
                'title'  => 'access denied exception',
                'detail' => 'No access to the entity.'
            ],
            $response,
            Response::HTTP_FORBIDDEN
        );
    }

    public function testTryToCreate()
    {
        $response = $this->post(
            ['entity' => 'customerusers'],
            'create_customer_user_min.yml',
            [],
            false
        );
        $this->assertResponseValidationError(
            [
                'title'  => 'access denied exception',
                'detail' => 'No access to this type of entities.'
            ],
            $response,
            Response::HTTP_FORBIDDEN
        );
    }

    public function testTryToUpdateCurrentLoggedInUser()
    {
        $customerUser = $this->getReference('customer_user');
        $this->disableProfileUpdatePermission($customerUser);
        $customerUserId = $customerUser->getId();

        $response = $this->patch(
            ['entity' => 'customerusers', 'id' => $customerUserId],
            [
                'data' => [
                    'type'       => 'customerusers',
                    'id'         => (string)$customerUserId,
                    'attributes' => [
                        'firstName' => 'Updated First Name'
                    ]
                ]
            ],
            [],
            false
        );
        $this->assertResponseValidationError(
            [
                'title'  => 'access denied exception',
                'detail' => 'No access to this type of entities.'
            ],
            $response,
            Response::HTTP_FORBIDDEN
        );
    }

    public function testTryToUpdateAnotherUser()
    {
        $customerUserId = $this->getReference('customer_user1')->getId();

        $response = $this->patch(
            ['entity' => 'customerusers', 'id' => $customerUserId],
            [
                'data' => [
                    'type'       => 'customerusers',
                    'id'         => (string)$customerUserId,
                    'attributes' => [
                        'firstName' => 'Updated First Name'
                    ]
                ]
            ],
            [],
            false
        );
        $this->assertResponseValidationError(
            [
                'title'  => 'access denied exception',
                'detail' => 'No access to this type of entities.'
            ],
            $response,
            Response::HTTP_FORBIDDEN
        );
    }

    public function testTryToDeleteCurrentLoggedInUser()
    {
        $customerUserId = $this->getReference('customer_user')->getId();

        $response = $this->delete(
            ['entity' => 'customerusers', 'id' => $customerUserId],
            [],
            [],
            false
        );
        $this->assertResponseValidationError(
            [
                'title'  => 'access denied exception',
                'detail' => 'No access to this type of entities.'
            ],
            $response,
            Response::HTTP_FORBIDDEN
        );
    }

    public function testTryToDeleteAnotherUser()
    {
        $customerUserId = $this->getReference('customer_user1')->getId();

        $response = $this->delete(
            ['entity' => 'customerusers', 'id' => $customerUserId],
            [],
            [],
            false
        );
        $this->assertResponseValidationError(
            [
                'title'  => 'access denied exception',
                'detail' => 'No access to this type of entities.'
            ],
            $response,
            Response::HTTP_FORBIDDEN
        );
    }

    public function testTryToDeleteList()
    {
        $response = $this->cdelete(
            ['entity' => 'customerusers'],
            ['filter[email]' => 'user2@example.com'],
            [],
            false
        );
        $this->assertResponseValidationError(
            [
                'title'  => 'access denied exception',
                'detail' => 'No access to this type of entities.'
            ],
            $response,
            Response::HTTP_FORBIDDEN
        );
    }

    public function testTryToDeleteListForCurrentLoggedInUser()
    {
        $response = $this->cdelete(
            ['entity' => 'customerusers'],
            ['filter[id]' => '<toString(@customer_user->id)>'],
            [],
            false
        );
        $this->assertResponseValidationError(
            [
                'title'  => 'access denied exception',
                'detail' => 'No access to this type of entities.'
            ],
            $response,
            Response::HTTP_FORBIDDEN
        );
    }
}
