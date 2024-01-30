<?php

namespace Oro\Bundle\WebsiteBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Migrations\Data\ORM\LoadCustomerUserRoles;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Loading website data.
 */
class LoadWebsiteData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public const DEFAULT_WEBSITE_NAME = 'Default';

    /**
     * {@inheritDoc}
     */
    public function getDependencies(): array
    {
        return [
            LoadOrganizationAndBusinessUnitData::class,
            LoadCustomerUserRoles::class
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): void
    {
        /** @var OrganizationInterface $organization */
        if ($this->hasReference('default_organization')) {
            $organization = $this->getReference('default_organization');
        } else {
            /**
             * Get first organization when install OroCommerce over OroCRM
             */
            $organization = $manager->getRepository(Organization::class)->getFirst();
        }

        $businessUnit = $manager->getRepository(BusinessUnit::class)
            ->findOneBy(['name' => LoadOrganizationAndBusinessUnitData::MAIN_BUSINESS_UNIT]);

        $website = new Website();
        $website->setName(self::DEFAULT_WEBSITE_NAME);
        $website->setOrganization($organization);
        $website->setOwner($businessUnit);
        $website->setGuestRole($this->getReference(LoadCustomerUserRoles::WEBSITE_GUEST_ROLE));
        $website->setDefaultRole($this->getReference(LoadCustomerUserRoles::WEBSITE_DEFAULT_ROLE));
        $website->setDefault(true);

        $manager->persist($website);
        $manager->flush($website);
    }
}
