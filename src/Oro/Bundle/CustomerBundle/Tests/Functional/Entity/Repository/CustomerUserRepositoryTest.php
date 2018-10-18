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
    /** @var CustomerUserRepository */
    private $repository;

    protected function setUp()
    {
        $this->initClient();

        $this->repository = $this->getContainer()
            ->get('doctrine')
            ->getRepository(CustomerUser::class);
    }

    public function testFindUserByEmailSensitive()
    {
        $this->loadFixtures([LoadCustomerUserData::class]);

        /** @var CustomerUser $user */
        $user = $this->getReference(LoadCustomerUserData::EMAIL);

        $this->assertEquals($user, $this->repository->findUserByEmail(strtoupper($user->getEmail()), true));
        $this->assertEquals($user, $this->repository->findUserByEmail(ucfirst($user->getEmail()), true));
        $this->assertEquals($user, $this->repository->findUserByEmail($user->getEmail(), true));
    }

    public function testFindUserByEmailInsensitive()
    {
        $this->loadFixtures([LoadCustomerUserData::class]);

        /** @var CustomerUser $user */
        $user = $this->getReference(LoadCustomerUserData::EMAIL);

        $this->assertTrue(null === $this->repository->findUserByEmail(strtoupper($user->getEmail()), false));
        $this->assertTrue(null === $this->repository->findUserByEmail(ucfirst($user->getEmail()), false));
        $this->assertEquals($user, $this->repository->findUserByEmail($user->getEmail(), false));
    }

    public function testFindLowercaseNonDuplicatedEmails()
    {
        $this->loadFixtures([LoadUserAndGuestWithSameUsername::class]);

        $this->assertEmpty($this->repository->findLowercaseDuplicatedEmails(10));
    }

    public function testFindLowercaseDuplicatedEmails()
    {
        $this->loadFixtures([LoadUsersWithSameEmail::class]);

        $this->assertEquals(
            [LoadUsersWithSameEmail::SAME_EMAIL],
            $this->repository->findLowercaseDuplicatedEmails(10)
        );
    }
}
