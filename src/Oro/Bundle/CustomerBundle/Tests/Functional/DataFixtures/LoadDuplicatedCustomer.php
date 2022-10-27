<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\User;

class LoadDuplicatedCustomer extends LoadCustomers
{
    const DUPLICATED_CUSTOMER_NAME = 'CustomerUser CustomerUser';

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        parent::load($manager);

        /** @var User $owner */
        $owner = $this->getReference('user');
        $this->createCustomer($manager, self::DUPLICATED_CUSTOMER_NAME, $owner);

        $manager->flush();
    }
}
