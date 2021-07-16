<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\SecurityBundle\Model\Role;
use Oro\Bundle\UserBundle\Entity\BaseUserManager;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadTreeProviderCustomerUserData extends AbstractFixture implements
    DependentFixtureInterface,
    ContainerAwareInterface
{
    use ContainerAwareTrait;

    public const LEVEL_1_2_EMAIL = 'third_customer.user@test.com';
    public const LEVEL_1_2_PASSWORD = 'pass';

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [LoadCustomerUserData::class, LoadTreeProviderCustomers::class];
    }

    public function load(ObjectManager $manager): void
    {
        /** @var BaseUserManager $userManager */
        $userManager = $this->container->get('oro_customer_user.manager');
        /** @var User $owner */
        $owner = $this->getReference('user');
        /** @var Customer $customer */
        $customer = $this->getReference(LoadTreeProviderCustomers::CUSTOMER_LEVEL_1_2);
        /** @var Role $role */
        $role = $manager
            ->getRepository(CustomerUserRole::class)
            ->findOneBy(['role' => 'ROLE_FRONTEND_ADMINISTRATOR']);

        $entity = new CustomerUser();
        $entity
            ->setIsGuest(false)
            ->setCustomer($customer)
            ->setOwner($owner)
            ->setFirstName('FirstName')
            ->setLastName('LastName')
            ->setEmail(self::LEVEL_1_2_EMAIL)
            ->setEnabled(true)
            ->setOrganization($customer->getOrganization())
            ->setConfirmationToken('some_token')
            ->addUserRole($role)
            ->setPlainPassword(self::LEVEL_1_2_PASSWORD)
            ->setConfirmed(true);

        $this->setReference($entity->getEmail(), $entity);
        $userManager->updateUser($entity);

        $manager->flush();
    }
}
