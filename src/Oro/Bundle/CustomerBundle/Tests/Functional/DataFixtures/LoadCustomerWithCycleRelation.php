<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\User;

class LoadCustomerWithCycleRelation extends LoadCustomers
{
    public const CUSTOMER_LEVEL_1_DOT_4_DOT_1_DOT_1_DOT_1 = 'customer.level_1.4.1.1.1';
    public const CUSTOMER_LEVEL_1_DOT_4_DOT_1_DOT_1_DOT_2 = 'customer.level_1.4.1.1.2';
    public const CUSTOMER_LEVEL_1_DOT_4_DOT_1_DOT_1_DOT_3 = 'customer.level_1.4.1.1.3';

    #[\Override]
    public function load(ObjectManager $manager)
    {
        parent::load($manager);

        /** @var User $owner */
        $owner = $this->getReference('user');
        $group = $this->getCustomerGroup('customer_group.group3');

        $levelOneFourthOneOne = $this->createCustomer(
            $manager,
            self::CUSTOMER_LEVEL_1_DOT_4_DOT_1_DOT_1_DOT_1,
            $owner,
            null,
            $group
        );

        $levelTreeFourthOneTwo = $this->createCustomer(
            $manager,
            self::CUSTOMER_LEVEL_1_DOT_4_DOT_1_DOT_1_DOT_2,
            $owner,
            $levelOneFourthOneOne
        );

        $levelOneFourthOneOne->setParent($levelTreeFourthOneTwo);

        $levelTreeFourthOneThree = $this->createCustomer(
            $manager,
            self::CUSTOMER_LEVEL_1_DOT_4_DOT_1_DOT_1_DOT_3,
            $owner,
            $levelOneFourthOneOne
        );

        $levelTreeFourthOneThree->setParent($levelTreeFourthOneThree);

        $manager->flush();
    }
}
