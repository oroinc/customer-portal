<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\Demo\ORM;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * Loads customer users.
 */
class LoadCustomerUserDemoData extends AbstractLoadCustomerUserDemoData
{
    /**
     * @var Organization
     */
    private $organization;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [LoadCustomerDemoData::class];
    }

    /**
     * {@inheritdoc}
     */
    protected function getOrganization(ObjectManager $manager)
    {
        //Can not use reference here because this fixture is used in tests
        if (!$this->organization) {
            $this->organization = $manager->getRepository('OroOrganizationBundle:Organization')->findOneBy(
                [],
                ['id' => 'ASC']
            );
        }

        return $this->organization;
    }

    /**
     * {@inheritdoc}
     */
    protected function getWebsite(ObjectManager $manager)
    {
        return $manager->getRepository(Website::class)->findOneBy(['default' => true]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCustomerUsersCSV()
    {
        return '@OroCustomerBundle/Migrations/Data/Demo/ORM/data/customer-users.csv';
    }

    /**
     * {@inheritdoc}
     */
    protected function getCustomerUserRole($roleLabel, ObjectManager $manager)
    {
        return $this->container->get('doctrine')
            ->getManagerForClass('OroCustomerBundle:CustomerUserRole')
            ->getRepository('OroCustomerBundle:CustomerUserRole')
            ->findOneBy(['label' => $roleLabel, 'organization' => $this->getOrganization($manager)]);
    }
}
