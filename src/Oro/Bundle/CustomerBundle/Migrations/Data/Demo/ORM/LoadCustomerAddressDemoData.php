<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\AbstractDefaultTypedAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;

/**
 * Loads customer addresses.
 */
class LoadCustomerAddressDemoData extends AbstractLoadAddressDemoData implements DependentFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function getDependencies(): array
    {
        return [LoadCustomerUserDemoData::class];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): void
    {
        $locator = $this->container->get('file_locator');
        $filePath = $locator->locate('@OroCustomerBundle/Migrations/Data/Demo/ORM/data/customer-users.csv');
        if (is_array($filePath)) {
            $filePath = current($filePath);
        }

        $handler = fopen($filePath, 'r');
        $headers = fgetcsv($handler, 1000, ',');

        /** @var CustomerUser[] $customerUserByEmail */
        $customerUserByEmail = [];
        $customerUsers = $this->getDemoCustomerUsers();
        foreach ($customerUsers as $customerUser) {
            $customerUserByEmail[$customerUser->getEmail()] = $customerUser;
        }

        $customerHasAddress = [];

        while (($data = fgetcsv($handler, 1000, ',')) !== false) {
            $row = array_combine($headers, array_values($data));
            $customerUser = $customerUserByEmail[$row['email']];
            if (isset($customerHasAddress[$customerUser->getCustomer()->getId()])) {
                continue;
            }
            $customerUser
                ->getCustomer()
                ->addAddress($this->createAddress($row));
            $customerHasAddress[$customerUser->getCustomer()->getId()] = true;
        }

        fclose($handler);
        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    protected function getNewAddressEntity(): AbstractDefaultTypedAddress
    {
        return new CustomerAddress();
    }

    /**
     * @return CustomerUser[]
     */
    private function getDemoCustomerUsers(): array
    {
        $customerUsers = [];
        foreach (LoadCustomerUserDemoData::$customerUsersReferencesNames as $referenceName) {
            $customerUsers[] = $this->getReference($referenceName);
        }

        return $customerUsers;
    }
}
