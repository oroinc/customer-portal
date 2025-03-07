<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\AbstractDefaultTypedAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;

/**
 * Loads customer user addresses.
 */
class LoadCustomerUserAddressDemoData extends AbstractLoadAddressDemoData implements DependentFixtureInterface
{
    #[\Override]
    public function getDependencies(): array
    {
        return [LoadCustomerUserDemoData::class];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $userManager = $this->container->get('oro_customer_user.manager');

        $locator = $this->container->get('file_locator');
        $filePath = $locator->locate('@OroCustomerBundle/Migrations/Data/Demo/ORM/data/customer-users.csv');
        if (is_array($filePath)) {
            $filePath = current($filePath);
        }

        $handler = fopen($filePath, 'r');
        $headers = fgetcsv($handler, 1000, ',');

        while (($data = fgetcsv($handler, 1000, ',')) !== false) {
            $row = array_combine($headers, array_values($data));

            $referenceName = LoadCustomerUserDemoData::ACCOUNT_USERS_REFERENCE_PREFIX . $row['email'];
            if (!$this->hasReference($referenceName)) {
                continue;
            }

            /** @var CustomerUser $customerUser */
            $customerUser = $this->getReference($referenceName);
            $customerUser
                ->addAddress($this->createAddress($row));

            $userManager->updateUser($customerUser, false);
        }

        $manager->flush();

        fclose($handler);
    }

    #[\Override]
    protected function getNewAddressEntity(): AbstractDefaultTypedAddress
    {
        return new CustomerUserAddress();
    }
}
