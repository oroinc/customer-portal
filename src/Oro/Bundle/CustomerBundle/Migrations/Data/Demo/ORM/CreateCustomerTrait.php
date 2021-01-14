<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\Demo\ORM;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Logic for creating Customer which is used in multiple places
 */
trait CreateCustomerTrait
{
    /**
     * @param ObjectManager $manager
     * @param string $customerName
     * @param User $owner
     * @param CustomerGroup $customerGroup
     * @param $internalRating
     * @param Organization $organization
     * @param Customer|null $parent
     * @return Customer
     */
    protected function createCustomer(
        ObjectManager $manager,
        string $customerName,
        User $owner,
        CustomerGroup $customerGroup,
        $internalRating,
        Organization $organization,
        Customer $parent = null
    ) {
        $customer = new Customer();
        $customer
            ->setName($customerName)
            ->setGroup($customerGroup)
            ->setParent($parent)
            ->setOrganization($organization)
            ->setOwner($owner)
            ->setInternalRating($internalRating);

        $manager->persist($customer);

        return $customer;
    }
}
