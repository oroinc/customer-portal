<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Entity\Repository;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRoleRepository;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserRoleData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CustomerUserRoleRepositoryTest extends WebTestCase
{
    /**
     * @var CustomerUserRoleRepository
     */
    protected $repository;

    /**
     * @var int
     */
    protected static $defaultRolesCount;

    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
        $this->repository = $this->getContainer()
            ->get('doctrine')
            ->getRepository('OroCustomerBundle:CustomerUserRole');

        if (null === self::$defaultRolesCount) {
            self::$defaultRolesCount = (int)$this->repository->createQueryBuilder('r')
                ->select('count(r)')
                ->getQuery()
                ->getSingleScalarResult();
        }
        $this->loadFixtures(['Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserRoleData']);
    }

    public function testIsDefaultOrGuestForWebsite()
    {
        $this->assertTrue($this->repository->isDefaultOrGuestForWebsite(
            $this->getReference(LoadCustomerUserRoleData::ROLE_WITH_WEBSITE)
        ));
        $this->assertTrue($this->repository->isDefaultOrGuestForWebsite(
            $this->getReference(LoadCustomerUserRoleData::ROLE_GUEST_FOR_WEBSITE)
        ));
        $this->assertFalse($this->repository->isDefaultOrGuestForWebsite(
            $this->getReference(LoadCustomerUserRoleData::ROLE_EMPTY)
        ));
    }

    public function testHasAssignedUsers()
    {
        /** @var CustomerUserRole $role */
        $role = $this->getReference(LoadCustomerUserRoleData::ROLE_WITH_ACCOUNT_USER);

        $hasAssignedUsers = $this->repository->hasAssignedUsers($role);
        $this->assertTrue($hasAssignedUsers);
    }

    public function testGetAssignedUsers()
    {
        /** @var CustomerUserRole $role */
        $role = $this->getReference(LoadCustomerUserRoleData::ROLE_WITH_ACCOUNT_USER);
        $assignedUsers = $this->repository->getAssignedUsers($role);
        $expectedUsers = [
            $this->getReference(LoadCustomerUserData::EMAIL)
        ];

        $this->assertEquals($expectedUsers, $assignedUsers);
    }

    public function testRoleWithoutUserAndWebsite()
    {
        /** @var CustomerUserRole $role */
        $role = $this->getReference(LoadCustomerUserRoleData::ROLE_EMPTY);

        $hasAssignedUsers = $this->repository->hasAssignedUsers($role);
        $this->assertFalse($hasAssignedUsers);

        $isDefaultForWebsite = $this->repository->isDefaultOrGuestForWebsite($role);
        $this->assertFalse($isDefaultForWebsite);
    }

    /**
     * @dataProvider customerUserRolesDataProvider
     * @param string $customerUser
     * @param array $expectedCustomerUserRoles
     */
    public function testGetAvailableRolesByCustomerUserQueryBuilder($customerUser, array $expectedCustomerUserRoles)
    {
        /** @var CustomerUser $customerUser */
        $customerUser = $this->getReference($customerUser);
        /** @var CustomerUserRole[] $actual */
        $actual = $this->repository
            ->getAvailableRolesByCustomerUserQueryBuilder(
                $customerUser->getOrganization(),
                $customerUser->getCustomer()
            )
            ->getQuery()
            ->getResult();
        $this->assertCount(count($expectedCustomerUserRoles) +  self::$defaultRolesCount, $actual);
        $roleIds = [];
        foreach ($actual as $role) {
            $roleIds[] = $role->getId();
        }
        foreach ($expectedCustomerUserRoles as $roleReference) {
            $this->assertContains($this->getReference($roleReference)->getId(), $roleIds);
        }
    }

    /**
     * @dataProvider customerUserRolesDataProvider
     * @param string $customerUser
     */
    public function testGetAvailableSelfManagedRolesByCustomerUserQueryBuilder(
        $customerUser
    ) {
        /** @var CustomerUser $customerUser */
        $customerUser = $this->getReference($customerUser);
        /** @var CustomerUserRole[] $actual */
        $actual = $this->repository
            ->getAvailableSelfManagedRolesByCustomerUserQueryBuilder(
                $customerUser->getOrganization(),
                $customerUser->getCustomer()
            )
            ->getQuery()
            ->getResult();

        $roleIds = [];

        foreach ($actual as $role) {
            $roleIds[] = $role->getId();
        }

        $this->assertNotContains(
            $this->getReference(LoadCustomerUserRoleData::ROLE_NOT_SELF_MANAGED)->getId(),
            $roleIds
        );
    }

    /**
     * @return array
     */
    public function customerUserRolesDataProvider()
    {
        return [
            'user from customer with custom role' => [
                'grzegorz.brzeczyszczykiewicz@example.com',
                [
                    LoadCustomerUserRoleData::ROLE_WITH_ACCOUNT,
                    LoadCustomerUserRoleData::ROLE_WITH_ACCOUNT_USER,
                    LoadCustomerUserRoleData::ROLE_WITH_WEBSITE,
                    LoadCustomerUserRoleData::ROLE_GUEST_FOR_WEBSITE,
                    LoadCustomerUserRoleData::ROLE_EMPTY,
                    LoadCustomerUserRoleData::ROLE_NOT_SELF_MANAGED,
                    LoadCustomerUserRoleData::ROLE_SELF_MANAGED,
                    LoadCustomerUserRoleData::ROLE_NOT_PUBLIC,
                ]
            ],
            'user from customer without custom roles' => [
                'orphan.user@test.com',
                [
                    LoadCustomerUserRoleData::ROLE_WITH_ACCOUNT_USER,
                    LoadCustomerUserRoleData::ROLE_WITH_WEBSITE,
                    LoadCustomerUserRoleData::ROLE_GUEST_FOR_WEBSITE,
                    LoadCustomerUserRoleData::ROLE_EMPTY,
                    LoadCustomerUserRoleData::ROLE_NOT_SELF_MANAGED,
                    LoadCustomerUserRoleData::ROLE_SELF_MANAGED,
                    LoadCustomerUserRoleData::ROLE_NOT_PUBLIC,
                ]
            ]
        ];
    }
}
