<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Command;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadExpiredGuestCustomerUsersData;
use Oro\Bundle\EmailBundle\Entity\EmailAddress;
use Oro\Bundle\EmailBundle\Entity\Manager\EmailAddressManager;
use Oro\Bundle\ScopeBundle\Entity\Scope;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * @dbIsolationPerTest
 */
final class ClearExpiredGuestCustomerUsersCommandTest extends WebTestCase
{
    private ObjectManager $em;

    #[\Override]
    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadExpiredGuestCustomerUsersData::class]);

        $this->em = $this->getContainer()->get('doctrine')->getManagerForClass(CustomerUser::class);
    }

    public function testShouldClearOnlyExpiredOrphanGuestCustomerUsers(): void
    {
        $orphanExpired = $this->getReference(LoadExpiredGuestCustomerUsersData::ORPHAN_GUEST_EXPIRED);
        $orphanNotExpired = $this->getReference(LoadExpiredGuestCustomerUsersData::ORPHAN_GUEST_NOT_EXPIRED);
        $guestWithOrder = $this->getReference(LoadExpiredGuestCustomerUsersData::GUEST_WITH_ORDER_EXPIRED);
        $guestWithShoppingList = $this->getReference(
            LoadExpiredGuestCustomerUsersData::GUEST_WITH_SHOPPING_LIST_EXPIRED
        );
        $registered = $this->getReference(LoadExpiredGuestCustomerUsersData::REGISTERED_EXPIRED);

        $orphanExpiredCustomerId = $orphanExpired->getCustomer()->getId();

        $result = $this->runCommand('oro:cron:customer-user:clear-expired-guests');
        self::assertStringContainsString('Clear expired guest customer users completed', $result);

        $this->em->clear();

        self::assertNull($this->findCustomerUser($orphanExpired->getId()));
        self::assertNull($this->findCustomer($orphanExpiredCustomerId));

        self::assertNotNull($this->findCustomerUser($orphanNotExpired->getId()));
        self::assertNotNull($this->findCustomerUser($guestWithOrder->getId()));
        self::assertNotNull($this->findCustomerUser($guestWithShoppingList->getId()));
        self::assertNotNull($this->findCustomerUser($registered->getId()));
    }

    public function testShouldNotClearExpiredGuestCustomerUserWithNonEmptyShoppingList(): void
    {
        $guestWithShoppingList = $this->getReference(
            LoadExpiredGuestCustomerUsersData::GUEST_WITH_SHOPPING_LIST_EXPIRED
        );

        $this->runCommand('oro:cron:customer-user:clear-expired-guests');

        $this->em->clear();

        $persistedGuest = $this->findCustomerUser($guestWithShoppingList->getId());
        self::assertNotNull(
            $persistedGuest,
            'Guest customer user with a non-empty shopping list must not be deleted, even if expired'
        );
    }

    public function testShouldClearExpiredGuestCustomerUserWithAutoCreatedScope(): void
    {
        $orphanExpired = $this->getReference(LoadExpiredGuestCustomerUsersData::ORPHAN_GUEST_EXPIRED);
        $customer = $orphanExpired->getCustomer();
        $customerId = $customer->getId();

        $website = $this->em->getRepository(Website::class)->findOneBy([]);

        $scope = new Scope();
        $scope->setCustomer($customer);
        $scope->setWebsite($website);
        $this->em->persist($scope);
        $this->em->flush();

        $this->runCommand('oro:cron:customer-user:clear-expired-guests');

        $this->em->clear();

        self::assertNull(
            $this->findCustomerUser($orphanExpired->getId()),
            'An oro_scope row not referenced by any ON DELETE NO ACTION table must not block removal'
        );
        self::assertNull($this->findCustomer($customerId));
    }

    public function testShouldKeepEmailAddressButRemoveOwnerWhenGuestCustomerUserIsCleared(): void
    {
        $orphanExpired = $this->getReference(LoadExpiredGuestCustomerUsersData::ORPHAN_GUEST_EXPIRED);
        $email = $orphanExpired->getEmail();

        $emailAddressManager = $this->getEmailAddressManager();
        $emailAddressRepository = $emailAddressManager->getEmailAddressRepository();

        $emailAddress = $emailAddressRepository->findOneBy(['email' => $email]);
        if (null === $emailAddress) {
            $emailAddress = $emailAddressManager->newEmailAddress()->setEmail($email);
        }
        $emailAddress->setOwner($orphanExpired);

        $emailAddressManager->getEntityManager()->persist($emailAddress);
        $emailAddressManager->getEntityManager()->flush();

        self::assertTrue($emailAddress->getHasOwner());
        self::assertNotNull($emailAddress->getOwner());

        $this->runCommand('oro:cron:customer-user:clear-expired-guests');

        $emailAddressManager->getEntityManager()->clear();

        /** @var EmailAddress|null $emailAddressAfter */
        $emailAddressAfter = $emailAddressRepository->findOneBy(['email' => $email]);
        self::assertNotNull(
            $emailAddressAfter,
            'Email address record must be detached from the owner, not deleted'
        );
        self::assertFalse($emailAddressAfter->getHasOwner());
        self::assertNull($emailAddressAfter->getOwner());
    }

    private function findCustomerUser(int $id): ?CustomerUser
    {
        return $this->em->getRepository(CustomerUser::class)->find($id);
    }

    private function findCustomer(int $id): ?Customer
    {
        return $this->em->getRepository(Customer::class)->find($id);
    }

    private function getEmailAddressManager(): EmailAddressManager
    {
        return self::getContainer()->get('oro_email.email.address.manager');
    }
}
