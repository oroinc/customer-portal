<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\Persistence\ObjectManager;

class LoadDuplicatedCustomer extends LoadCustomers
{
    const DUPLICATED_CUSTOMER_NAME = 'CustomerUser CustomerUser';

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        parent::load($manager);

        $owner = $this->getFirstUser($manager);
        $this->createCustomer($manager, self::DUPLICATED_CUSTOMER_NAME, $owner);

        $manager->flush();
    }
}
