<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use Oro\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductUnitPrecisions;
use Oro\Bundle\ShoppingListBundle\Entity\LineItem;
use Oro\Bundle\ShoppingListBundle\Entity\ShoppingList;
use Oro\Bundle\UserBundle\Entity\BaseUserManager;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Loads guest and registered customer users with different "updated at" dates, and one guest with a
 * related order, to test the expired guest customer users cleanup command.
 */
class LoadExpiredGuestCustomerUsersData extends AbstractFixture implements
    ContainerAwareInterface,
    DependentFixtureInterface
{
    use ContainerAwareTrait;

    public const string ORPHAN_GUEST_EXPIRED = 'orphan_guest_customer_user_expired';
    public const string ORPHAN_GUEST_NOT_EXPIRED = 'orphan_guest_customer_user_not_expired';
    public const string GUEST_WITH_ORDER_EXPIRED = 'guest_customer_user_with_order_expired';
    public const string GUEST_WITH_SHOPPING_LIST_EXPIRED = 'guest_customer_user_with_shopping_list_expired';
    public const string REGISTERED_EXPIRED = 'registered_customer_user_expired';

    #[\Override]
    public function getDependencies(): array
    {
        return [LoadProductUnitPrecisions::class];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $organization = $manager->getRepository(Organization::class)->findOneBy([]);
        $owner = $manager->getRepository(User::class)->findOneBy([]);

        $expiredDate = new \DateTime('-1 year', new \DateTimeZone('UTC'));
        $notExpiredDate = new \DateTime('now', new \DateTimeZone('UTC'));

        $orphanExpired = $this->createGuestCustomerUser(
            'orphan_guest_expired@example.com',
            $organization,
            $expiredDate
        );
        $this->setReference(self::ORPHAN_GUEST_EXPIRED, $orphanExpired);

        $orphanNotExpired = $this->createGuestCustomerUser(
            'orphan_guest_not_expired@example.com',
            $organization,
            $notExpiredDate
        );
        $this->setReference(self::ORPHAN_GUEST_NOT_EXPIRED, $orphanNotExpired);

        $guestWithOrder = $this->createGuestCustomerUser(
            'guest_with_order_expired@example.com',
            $organization,
            $expiredDate
        );
        $this->setReference(self::GUEST_WITH_ORDER_EXPIRED, $guestWithOrder);

        $guestWithShoppingList = $this->createGuestCustomerUser(
            'guest_with_shopping_list_expired@example.com',
            $organization,
            $expiredDate
        );
        $this->setReference(self::GUEST_WITH_SHOPPING_LIST_EXPIRED, $guestWithShoppingList);

        $registered = $this->createRegisteredCustomerUser(
            'registered_expired@example.com',
            $organization,
            $owner,
            $expiredDate
        );
        $this->setReference(self::REGISTERED_EXPIRED, $registered);

        $manager->flush();

        $order = new Order();
        $order->setIdentifier('guest-with-order-expired');
        $order->setCustomer($guestWithOrder->getCustomer());
        $order->setCustomerUser($guestWithOrder);
        $order->setOrganization($organization);
        $order->setOwner($owner);
        $order->setCurrency('USD');
        $manager->persist($order);

        $this->createShoppingListWithLineItem($manager, $guestWithShoppingList, $organization);

        $manager->flush();

        $this->setUpdatedAt(
            $manager,
            [$orphanExpired, $guestWithOrder, $guestWithShoppingList, $registered],
            $expiredDate
        );
        $this->setUpdatedAt($manager, [$orphanNotExpired], $notExpiredDate);
    }

    private function createShoppingListWithLineItem(
        ObjectManager $manager,
        CustomerUser $customerUser,
        Organization $organization
    ): void {
        $shoppingList = new ShoppingList();
        $shoppingList->setOrganization($organization);
        $shoppingList->setLabel('guest_with_shopping_list_expired_label');
        $shoppingList->setCurrency('USD');
        $shoppingList->setCustomerUser($customerUser);

        $lineItem = new LineItem();
        $lineItem->setProduct($this->getReference(LoadProductData::PRODUCT_1));
        $lineItem->setUnit($this->getReference('product_unit.bottle'));
        $lineItem->setQuantity(2);
        $lineItem->setOrganization($organization);
        $lineItem->setCustomerUser($customerUser);

        $shoppingList->addLineItem($lineItem);

        $manager->persist($shoppingList);
    }

    private function createGuestCustomerUser(
        string $email,
        Organization $organization,
        \DateTime $updatedAt
    ): CustomerUser {
        /** @var BaseUserManager $userManager */
        $userManager = $this->container->get('oro_customer_user.manager');

        $customerUser = new CustomerUser();
        $customerUser->setIsGuest(true)
            ->setEmail($email)
            ->setFirstName('Guest')
            ->setLastName('User')
            ->setEnabled(true)
            ->setOrganization($organization)
            ->setUpdatedAt($updatedAt)
            ->setPlainPassword('guest_cleanup_password');
        $customerUser->createCustomer();
        $customerUser->getCustomer()->setOrganization($organization);

        $userManager->updateUser($customerUser, false);

        return $customerUser;
    }

    private function createRegisteredCustomerUser(
        string $email,
        Organization $organization,
        User $owner,
        \DateTime $updatedAt
    ): CustomerUser {
        /** @var BaseUserManager $userManager */
        $userManager = $this->container->get('oro_customer_user.manager');

        $customerUser = new CustomerUser();
        $customerUser->setIsGuest(false)
            ->setEmail($email)
            ->setFirstName('Registered')
            ->setLastName('User')
            ->setEnabled(true)
            ->setOwner($owner)
            ->setOrganization($organization)
            ->setUpdatedAt($updatedAt)
            ->setPlainPassword('registered_cleanup_password');
        $customerUser->createCustomer();
        $customerUser->getCustomer()->setOrganization($organization);

        $userManager->updateUser($customerUser, false);

        return $customerUser;
    }

    /**
     * @param CustomerUser[] $customerUsers
     */
    private function setUpdatedAt(ObjectManager $manager, array $customerUsers, \DateTime $updatedAt): void
    {
        $ids = array_map(static fn (CustomerUser $cu) => $cu->getId(), $customerUsers);

        $manager->getRepository(CustomerUser::class)->createQueryBuilder('cu')
            ->update(CustomerUser::class, 'cu')
            ->set('cu.updatedAt', ':updatedAt')
            ->where('cu.id IN (:ids)')
            ->setParameter('updatedAt', $updatedAt)
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
    }
}
