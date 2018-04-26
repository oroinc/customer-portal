<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserApi;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;
use Oro\Bundle\UserBundle\Entity\BaseUserManager;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Creates the customer user entity with administrative permissions that can be used to test frontend REST API.
 */
class LoadFrontendApiCustomerUserData extends AbstractFixture implements
    DependentFixtureInterface,
    ContainerAwareInterface
{
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
        return [LoadUser::class];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $owner = $this->getReference('user');

        $customer = $this->createCustomer($manager, $owner);
        $this->addReference('customer', $customer);
        $customerUser = $this->createCustomerUser($customer, $owner);
        $this->addReference('customer_user', $customerUser);

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @param User          $owner
     *
     * @return Customer
     */
    protected function createCustomer(ObjectManager $manager, User $owner)
    {
        $customer = new Customer();
        $customer->setName('Customer');
        $customer->setOwner($owner);
        $customer->setOrganization($owner->getOrganization());
        $manager->persist($customer);

        return $customer;
    }

    /**
     * @param Customer $customer
     * @param User     $owner
     *
     * @return CustomerUser
     */
    protected function createCustomerUser(Customer $customer, User $owner)
    {
        /** @var BaseUserManager $userManager */
        $userManager = $this->container->get('oro_customer_user.manager');

        /** @var CustomerUser $customerUser */
        $customerUser = $userManager->createUser();

        $role = $this->container
            ->get('doctrine')
            ->getManagerForClass(CustomerUserRole::class)
            ->getRepository(CustomerUserRole::class)
            ->findOneBy(['role' => 'ROLE_FRONTEND_ADMINISTRATOR']);

        $customerUser
            ->setFirstName('John')
            ->setLastName('Doe')
            ->setEmail(FrontendRestJsonApiTestCase::USER_NAME)
            ->setPlainPassword(FrontendRestJsonApiTestCase::AUTH_PW)
            ->setSalt('')
            ->setOwner($owner)
            ->setOrganization($owner->getOrganization())
            ->addRole($role)
            ->setCustomer($customer);

        $apiKey = new CustomerUserApi();
        $apiKey
            ->setApiKey(FrontendRestJsonApiTestCase::USER_PASSWORD)
            ->setUser($customerUser);
        $customerUser->addApiKey($apiKey);

        $userManager->updateUser($customerUser, false);

        return $customerUser;
    }
}
