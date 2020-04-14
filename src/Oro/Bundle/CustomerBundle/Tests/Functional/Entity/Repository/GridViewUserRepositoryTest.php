<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Entity\Repository;

use Oro\Bundle\CustomerBundle\Entity\GridViewUser;
use Oro\Bundle\CustomerBundle\Entity\Repository\GridViewUserRepository;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserGridViewACLData;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadGridViewUserData;
use Oro\Bundle\DataGridBundle\Tests\Functional\Entity\Repository\GridViewUserRepositoryTest as BaseTest;

class GridViewUserRepositoryTest extends BaseTest
{
    /** @var GridViewUserRepository */
    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([LoadGridViewUserData::class]);

        $this->repository = $this->getContainer()->get('doctrine')->getRepository(GridViewUser::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function getUsername()
    {
        return LoadCustomerUserGridViewACLData::USER_ACCOUNT_2_ROLE_LOCAL;
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserRepository()
    {
        return $this->getContainer()
            ->get('doctrine')
            ->getManagerForClass('OroCustomerBundle:CustomerUser')
            ->getRepository('OroCustomerBundle:CustomerUser');
    }
}
