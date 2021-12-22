<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\GridView;
use Oro\Bundle\CustomerBundle\Entity\Repository\GridViewRepository;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserGridViewACLData;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadGridViewUserData;
use Oro\Bundle\DataGridBundle\Tests\Functional\Entity\Repository\GridViewRepositoryTest as BaseTest;

class GridViewRepositoryTest extends BaseTest
{
    /** @var GridViewRepository */
    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([LoadGridViewUserData::class]);

        $this->repository = $this->getContainer()->get('doctrine')->getRepository(GridView::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function getUsername(): string
    {
        return LoadCustomerUserGridViewACLData::USER_ACCOUNT_2_ROLE_LOCAL;
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserRepository(): EntityRepository
    {
        return self::getContainer()->get('doctrine')->getRepository(CustomerUser::class);
    }
}
