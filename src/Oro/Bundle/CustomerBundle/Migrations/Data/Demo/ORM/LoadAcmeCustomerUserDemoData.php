<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Migrations\Data\ORM\LoadAcmeCustomerUserRoles;
use Oro\Bundle\OrganizationBundle\Migrations\Data\Demo\ORM\LoadAcmeOrganizationAndBusinessUnitData;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * Loads customer users demo data for acme organization.
 */
class LoadAcmeCustomerUserDemoData extends AbstractLoadCustomerUserDemoData
{

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [LoadAcmeCustomerDemoData::class, LoadAcmeCustomerUserRoles::class];
    }

    /**
     * {@inheritdoc}
     */
    protected function getOrganization(ObjectManager $manager)
    {
        return $this->getReference(LoadAcmeOrganizationAndBusinessUnitData::REFERENCE_DEMO_ORGANIZATION);
    }

    /**
     * {@inheritdoc}
     */
    protected function getWebsite(ObjectManager $manager)
    {
        return $manager->getRepository(Website::class)->findOneBy(['name' => 'ACME']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCustomerUsersCSV()
    {
        return '@OroCustomerBundle/Migrations/Data/Demo/ORM/data/acme_customer_users.csv';
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
