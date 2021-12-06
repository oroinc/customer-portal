<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Entity\Repository;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRepository;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadUserAndGuestWithSameUsername;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadUsersWithSameEmail;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CustomerUserRepositoryTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
    }

    private function getRepository(): CustomerUserRepository
    {
        return self::getContainer()->get('doctrine')->getRepository(CustomerUser::class);
    }

    public function testGetAssignableCustomerUserIds()
    {
        $user = $this->getRepository()->findOneBy([]);

        $this->assertEquals(
            [
                $user->getId()
            ],
            $this->getRepository()->getAssignableCustomerUserIds(
                $this->getContainer()->get('oro_security.acl_helper'),
                CustomerUser::class
            )
        );
    }

    public function testFindUserByEmailSensitive()
    {
        $this->loadFixtures([LoadCustomerUserData::class]);

        /** @var CustomerUser $user */
        $user = $this->getReference(LoadCustomerUserData::EMAIL);

        $this->assertEquals($user, $this->getRepository()->findUserByEmail(strtoupper($user->getEmail()), true));
        $this->assertEquals($user, $this->getRepository()->findUserByEmail(ucfirst($user->getEmail()), true));
        $this->assertEquals($user, $this->getRepository()->findUserByEmail($user->getEmail(), true));
    }

    public function testFindUserByEmailInsensitive()
    {
        $this->loadFixtures([LoadCustomerUserData::class]);

        /** @var CustomerUser $user */
        $user = $this->getReference(LoadCustomerUserData::EMAIL);

        $this->assertNull($this->getRepository()->findUserByEmail(strtoupper($user->getEmail()), false));
        $this->assertNull($this->getRepository()->findUserByEmail(ucfirst($user->getEmail()), false));
        $this->assertEquals($user, $this->getRepository()->findUserByEmail($user->getEmail(), false));
    }

    public function testFindUserByEmailAndOrganizationSensitive()
    {
        $this->loadFixtures([LoadCustomerUserData::class]);

        /** @var CustomerUser $user */
        $user = $this->getReference(LoadCustomerUserData::EMAIL);

        $this->assertEquals($user, $this->getRepository()
            ->findUserByEmailAndOrganization(strtoupper($user->getEmail()), $user->getOrganization(), true));
        $this->assertEquals($user, $this->getRepository()
            ->findUserByEmailAndOrganization(ucfirst($user->getEmail()), $user->getOrganization(), true));
        $this->assertEquals($user, $this->getRepository()
            ->findUserByEmailAndOrganization($user->getEmail(), $user->getOrganization(), true));
    }

    public function testFindUserByEmailAndOrganizationInsensitive()
    {
        $this->loadFixtures([LoadCustomerUserData::class]);

        /** @var CustomerUser $user */
        $user = $this->getReference(LoadCustomerUserData::EMAIL);

        $this->assertNull($this->getRepository()
            ->findUserByEmailAndOrganization(strtoupper($user->getEmail()), $user->getOrganization(), false));
        $this->assertNull($this->getRepository()
            ->findUserByEmailAndOrganization(ucfirst($user->getEmail()), $user->getOrganization(), false));
        $this->assertEquals($user, $this->getRepository()
            ->findUserByEmailAndOrganization($user->getEmail(), $user->getOrganization(), false));
    }

    public function testFindLowercaseNonDuplicatedEmails()
    {
        $this->loadFixtures([LoadUserAndGuestWithSameUsername::class]);

        $this->assertEmpty($this->getRepository()->findLowercaseDuplicatedEmails(10));
    }

    public function testFindLowercaseDuplicatedEmails()
    {
        $this->loadFixtures([LoadUsersWithSameEmail::class]);

        $this->assertEquals(
            [LoadUsersWithSameEmail::SAME_EMAIL],
            $this->getRepository()->findLowercaseDuplicatedEmails(10)
        );
    }
}
