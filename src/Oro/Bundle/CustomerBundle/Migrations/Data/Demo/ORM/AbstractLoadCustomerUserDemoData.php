<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Loads customer users.
 */
abstract class AbstractLoadCustomerUserDemoData extends AbstractFixture implements
    ContainerAwareInterface,
    DependentFixtureInterface
{
    const ACCOUNT_USERS_REFERENCE_PREFIX = 'customer_user_demo_data_';

    /** @var ContainerInterface */
    protected $container;

    /** @var array */
    public static $customerUsersReferencesNames = [];

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Returns file path to csv file with CustomerUsers data
     * @return string
     */
    abstract protected function getCustomerUsersCSV();

    /**
     * Returns role for certain label
     * @param string $roleLabel
     * @param ObjectManager $manager
     * @return CustomerUserRole
     */
    abstract protected function getCustomerUserRole($roleLabel, ObjectManager $manager);

    /**
     * Returns organization entity for which CustomerUsers should be created
     * @param ObjectManager $manager
     * @return Organization
     */
    abstract protected function getOrganization(ObjectManager $manager);

    /**
     * Returns website for which CustomerUsers should be created
     * @param ObjectManager $manager
     * @return Website
     */
    abstract protected function getWebsite(ObjectManager $manager);

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $organization = $this->getOrganization($manager);

        /** @var Website $website */
        $website = $this->getWebsite($manager);

        /** @var \Oro\Bundle\CustomerBundle\Entity\CustomerUserManager $userManager */
        $userManager = $this->container->get('oro_customer_user.manager');

        $locator = $this->container->get('file_locator');
        $filePath = $locator->locate($this->getCustomerUsersCSV());
        if (is_array($filePath)) {
            $filePath = current($filePath);
        }

        $handler = fopen($filePath, 'r');
        $headers = fgetcsv($handler, 1000);

        $roles = [];

        while (($data = fgetcsv($handler, 1000, ',')) !== false) {
            $row = array_combine($headers, array_values($data));

            $customer = $this->getReference(LoadCustomerDemoData::ACCOUNT_REFERENCE_PREFIX . $row['customer']);
            if (!$customer) {
                continue;
            }

            // create/get customer user role
            $roleLabel = $row['role'];
            if (!array_key_exists($roleLabel, $roles)) {
                $roles[$roleLabel] = $this->getCustomerUserRole($roleLabel, $manager);
            }
            $role = $roles[$roleLabel];

            // create customer user
            /** @var CustomerUser $customerUser */
            $customerUser = $userManager->createUser();
            $customerUser
                ->setWebsite($website)
                ->setUsername($row['email'])
                ->setEmail($row['email'])
                ->setFirstName($row['firstName'])
                ->setLastName($row['lastName'])
                ->setPlainPassword($row['email'])
                ->setCustomer($customer)
                ->setOwner($customer->getOwner())
                ->setEnabled(true)
                ->setOrganization($organization)
                ->setLoginCount(0)
                ->addUserRole($role)
                ->setIsGuest($row['isGuest']);

            $userManager->updateUser($customerUser, false);

            $referenceName = self::ACCOUNT_USERS_REFERENCE_PREFIX . $row['email'];
            $this->addReference($referenceName, $customerUser);
            self::$customerUsersReferencesNames[] = $referenceName;
        }

        fclose($handler);
        $manager->flush();
    }
}
