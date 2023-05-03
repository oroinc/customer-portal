<?php

namespace Oro\Bundle\WebsiteBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Migrations\Data\ORM\LoadCustomerUserRoles;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Loading website data.
 */
class LoadWebsiteData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    const DEFAULT_WEBSITE_NAME = 'Default';

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return [
            LoadOrganizationAndBusinessUnitData::class,
            LoadCustomerUserRoles::class
        ];
    }

    public function load(ObjectManager $manager)
    {
        /** @var OrganizationInterface $organization */
        if ($this->hasReference('default_organization')) {
            $organization = $this->getReference('default_organization');
        } else {
            /**
             * Get first organization when install OroCommerce over OroCRM
             */
            $organization = $manager
                ->getRepository('OroOrganizationBundle:Organization')
                ->getFirst();
        }

        $businessUnit = $manager
            ->getRepository('OroOrganizationBundle:BusinessUnit')
            ->findOneBy(['name' => LoadOrganizationAndBusinessUnitData::MAIN_BUSINESS_UNIT]);

        $defaultRole = $this->getReference(LoadCustomerUserRoles::WEBSITE_DEFAULT_ROLE);
        $guestRole = $this->getReference(LoadCustomerUserRoles::WEBSITE_GUEST_ROLE);

        $website = new Website();
        $website
            ->setName(self::DEFAULT_WEBSITE_NAME)
            ->setOrganization($organization)
            ->setOwner($businessUnit)
            ->setGuestRole($guestRole)
            ->setDefaultRole($defaultRole)
            ->setDefault(true);

        $manager->persist($website);
        /** @var EntityManager $manager */
        $manager->flush($website);
    }
}
