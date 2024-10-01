<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUser;
use Oro\Bundle\TestFrameworkBundle\Test\DataFixtures\AbstractFixture;

class LoadCustomerUserRoleData extends AbstractFixture implements DependentFixtureInterface
{
    #[\Override]
    public function getDependencies(): array
    {
        return [LoadCustomerUser::class];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        /** @var CustomerUser $customerUser */
        $customerUser = $this->getReference(LoadCustomerUser::CUSTOMER_USER);
        for ($i = 1; $i <= 3; $i++) {
            $role = new CustomerUserRole();
            $role->setLabel('Role ' . $i);
            $role->setRole('ROLE_FRONTEND_' . $i, false);
            $role->setCustomer($customerUser->getCustomer());
            $role->setOrganization($customerUser->getOrganization());
            if (0 === ($i % 2)) {
                $role->setSelfManaged(true);
            }
            $manager->persist($role);
            $this->setReference('role' . $i, $role);
        }
        for ($i = 1; $i <= 2; $i++) {
            $customerUser->addUserRole($this->getReference('role' . $i));
        }
        $manager->flush();
    }
}
