<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserApi;
use Oro\Bundle\CustomerBundle\Tests\Functional\Api\DataFixtures\LoadCustomerUserRoles;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Creates the customer user entity.
 */
class LoadCustomerUserData extends AbstractFixture implements
    DependentFixtureInterface,
    ContainerAwareInterface
{
    protected const USER_NAME = 'frontend_admin_api@example.com';
    protected const USER_PASSWORD = 'frontend_admin_api_key';

    /** @var ContainerInterface */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [LoadUser::class, LoadCustomerData::class, LoadCustomerUserRoles::class];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var User $owner */
        $owner = $this->getReference('user');
        /** @var Customer $customer */
        $customer = $this->getReference('customer');

        $customerUser = $this->createCustomerUser($manager, $customer, $owner);
        $this->addReference('customer_user', $customerUser);

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @param Customer      $customer
     * @param User          $owner
     *
     * @return CustomerUser
     */
    protected function createCustomerUser(ObjectManager $manager, Customer $customer, User $owner)
    {
        $customerUser = new CustomerUser();

        $passwordHasher = $this->container->get('security.password_hasher_factory')->getPasswordHasher($customerUser);
        $customerUser
            ->setFirstName('John')
            ->setLastName('Doe')
            ->setEmail('test@example.com')
            ->setPlainPassword('test')
            ->setPassword($passwordHasher->hash('test'))
            ->setOwner($owner)
            ->setOrganization($owner->getOrganization())
            ->setCustomer($customer);

        $apiKey = new CustomerUserApi();
        $apiKey
            ->setApiKey(static::USER_PASSWORD)
            ->setUser($customerUser);
        $customerUser->addApiKey($apiKey);

        $this->initializeCustomerUser($customerUser);

        $manager->persist($customerUser);
        $manager->flush();

        return $customerUser;
    }

    protected function initializeCustomerUser(CustomerUser $customerUser)
    {
    }
}
