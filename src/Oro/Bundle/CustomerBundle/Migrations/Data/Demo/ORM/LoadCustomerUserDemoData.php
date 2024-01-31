<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\Demo\ORM;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * Loads customer users.
 */
class LoadCustomerUserDemoData extends AbstractLoadCustomerUserDemoData
{
    private ?Organization $organization = null;

    /**
     * {@inheritDoc}
     */
    public function getDependencies(): array
    {
        return [LoadCustomerDemoData::class];
    }

    /**
     * {@inheritDoc}
     */
    protected function getOrganization(ObjectManager $manager): Organization
    {
        //Can not use reference here because this fixture is used in tests
        if (!$this->organization) {
            $this->organization = $manager->getRepository(Organization::class)->findOneBy(
                [],
                ['id' => 'ASC']
            );
        }

        return $this->organization;
    }

    /**
     * {@inheritDoc}
     */
    protected function getWebsite(ObjectManager $manager): Website
    {
        return $manager->getRepository(Website::class)->findOneBy(['default' => true]);
    }

    /**
     * {@inheritDoc}
     */
    protected function getCustomerUsersCSV(): string
    {
        return '@OroCustomerBundle/Migrations/Data/Demo/ORM/data/customer-users.csv';
    }

    /**
     * {@inheritDoc}
     */
    protected function getCustomerUserRole($roleLabel, ObjectManager $manager): CustomerUserRole
    {
        return $manager->getRepository(CustomerUserRole::class)
            ->findOneBy(['label' => $roleLabel, 'organization' => $this->getOrganization($manager)]);
    }
}
