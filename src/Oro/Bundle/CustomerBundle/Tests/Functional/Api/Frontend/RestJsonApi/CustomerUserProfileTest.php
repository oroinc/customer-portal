<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\RestJsonApi;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\DataFixtures\LoadBayerCustomerUserProfileData;
use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;
use Oro\Bundle\SecurityBundle\Acl\Extension\EntityAclExtension;
use Oro\Bundle\SecurityBundle\Acl\Extension\ObjectIdentityHelper;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Symfony\Component\HttpFoundation\Response;

/**
 * @dbIsolationPerTest
 */
class CustomerUserProfileTest extends FrontendRestJsonApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
            LoadBayerCustomerUserProfileData::class,
            '@OroCustomerBundle/Tests/Functional/Api/Frontend/DataFixtures/customer_user.yml'
        ]);

        $roles = $this->getEntityManager()
            ->getRepository(CustomerUserRole::class)
            ->findAll();

        foreach ($roles as $role) {
            $this->getReferenceRepository()->setReference($role->getRole(), $role);
        }
    }

    public function testTryToUpdateCurrentLoggedInUserProfileWithoutProfilePermission(): void
    {
        $customerUser = $this->getReference('customer_user');
        // None permission to update customer user.
        $this->setEntityPermissions($customerUser, CustomerUser::class, []);
        $this->setProfilePermission($customerUser, false);

        $response = $this->patch(
            ['entity' => 'customerusers', 'id' => 'mine'],
            'update_customer_user_profile_first_name.yml',
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title' => 'access denied exception',
                'detail' => 'No access to this type of entities.'
            ],
            $response,
            Response::HTTP_FORBIDDEN
        );
    }

    public function testTryToUpdateAnyCustomerUserWithProfilePermission(): void
    {
        $customerUser = $this->getReference('customer_user1');
        $this->setEntityPermissions($customerUser, CustomerUser::class, []);
        $this->setProfilePermission($customerUser, true);

        $response = $this->patch(
            ['entity' => 'customerusers', 'id' => (string)$customerUser->getId()],
            'update_customer_user_first_name.yml',
            [],
            false
        );

        // Profile permissions do not apply to other customer users.
        $this->assertResponseValidationError(
            [
                'title' => 'access denied exception',
                'detail' => 'No access to this type of entities.'
            ],
            $response,
            Response::HTTP_FORBIDDEN
        );
    }

    public function testTryToUpdateCurrentLoggedInUserProfileWithProfilePermission(): void
    {
        $customerUser = $this->getReference('customer_user');
        // Only view permission for customer user.
        $this->setEntityPermissions($customerUser, CustomerUser::class, ['VIEW_SYSTEM']);
        $this->setProfilePermission($customerUser, true);

        $this->patch(
            ['entity' => 'customerusers', 'id' => 'mine'],
            'update_customer_user_profile_first_name.yml',
        );
    }

    public function testTryToUpdateCurrentLoggedInUserRoles(): void
    {
        /** @var CustomerUser $customerUser */
        $customerUser = $this->getReference('customer_user');

        $this->setEntityPermissions($customerUser, CustomerUser::class, ['VIEW_SYSTEM']);
        $this->setEntityPermissions($customerUser, Customer::class, ['VIEW_SYSTEM']);
        $this->setProfilePermission($customerUser);

        $response = $this->patch(
            ['entity' => 'customerusers', 'id' => 'mine'],
            'update_customer_user_profile_role.yml',
            [],
            false,
        );

        $this->assertResponseValidationError(
            [
                'status' => '400',
                'title' => 'unchangeable field constraint',
                'detail' => 'Field cannot be changed once set',
                'source' => ['pointer' => '/data/relationships/userRoles/data']
            ],
            $response
        );
    }

    private function setProfilePermission(CustomerUser $customerUser, bool $isGranted = true): void
    {
        /** @var AclManager $manager */
        $manager = self::getContainer()->get('oro_security.acl.manager');
        $oid = $manager->getOid('action: oro_customer_frontend_update_own_profile');
        foreach ($customerUser->getUserRoles() as $role) {
            $sid = $manager->getSid($role);
            $manager->setPermission($sid, $oid, (int)$isGranted);
            $manager->flush();
        }
    }

    private function setEntityPermissions(
        CustomerUser $customerUser,
        string $entityClass,
        array $permissions
    ): void {
        $aclManager = self::getContainer()->get('oro_security.acl.manager');
        foreach ($customerUser->getUserRoles() as $role) {
            $sid = $aclManager->getSid($role);
            $oid = $aclManager
                ->getOid(ObjectIdentityHelper::encodeIdentityString(EntityAclExtension::NAME, $entityClass));
            $maskBuilder = $aclManager->getMaskBuilder($oid);
            $maskBuilder->reset();
            foreach ($permissions as $permission) {
                $maskBuilder->add($permission);
            }
        }

        $aclManager->setPermission($sid, $oid, $maskBuilder->get());
    }
}
