<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\TestFrameworkBundle\Test\DataFixtures\InitialFixtureInterface;

/**
 * Loads customer user roles from the database.
 */
class LoadCustomerUserRoles extends AbstractFixture implements InitialFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $roleRepository = $manager->getRepository(CustomerUserRole::class);
        $this->addReference('buyer', $roleRepository->findOneBy(['role' => 'ROLE_FRONTEND_BUYER']));
        $this->addReference('admin', $roleRepository->findOneBy(['role' => 'ROLE_FRONTEND_ADMINISTRATOR']));
    }
}
