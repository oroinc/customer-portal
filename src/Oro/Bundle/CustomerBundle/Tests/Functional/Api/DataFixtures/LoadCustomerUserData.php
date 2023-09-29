<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadCustomerUserData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function getDependencies(): array
    {
        return [LoadOrganization::class, LoadUser::class];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): void
    {
        /** @var CustomerUserManager $userManager */
        $userManager = $this->container->get('oro_customer_user.manager');

        $role = $manager
            ->getRepository(CustomerUserRole::class)->findOneBy(['role' => 'ROLE_FRONTEND_ADMINISTRATOR']);
        $website = $manager->getRepository(Website::class)->findOneBy(['default' => true]);

        $organization = $this->getReference('organization');

        $customer = new Customer();
        $customer
            ->setName('test')
            ->setParent(null)
            ->setOrganization($organization)
            ->setOwner($this->getReference('user'));
        $manager->persist($customer);
        $manager->flush();

        $user = $userManager->createUser();
        $user
            ->setWebsite($website)
            ->setEmail('test@test.com')
            ->setFirstName('test')
            ->setLastName('test')
            ->setPlainPassword('test_password')
            ->setCustomer($customer)
            ->setOwner($this->getReference('user'))
            ->setEnabled(true)
            ->setOrganization($organization)
            ->setLoginCount(0)
            ->addUserRole($role);

        $userManager->updateUser($user);
        $this->setReference('customerUser', $user);
    }
}
