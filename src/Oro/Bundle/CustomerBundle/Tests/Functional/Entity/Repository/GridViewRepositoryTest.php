<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Entity\Repository;

use Oro\Bundle\CustomerBundle\Entity\GridView;
use Oro\Bundle\CustomerBundle\Entity\Repository\GridViewRepository;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserGridViewACLData;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadGridViewUserData;
use Oro\Bundle\DataGridBundle\Tests\Functional\Entity\Repository\GridViewRepositoryTest as BaseTest;

class GridViewRepositoryTest extends BaseTest
{
    /** @var GridViewRepository */
    protected $repository;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([LoadGridViewUserData::class]);
        $this->repository = self::getContainer()->get('doctrine')->getRepository(GridView::class);
    }

    #[\Override]
    protected function getUserReference(): string
    {
        return LoadCustomerUserGridViewACLData::USER_ACCOUNT_2_ROLE_LOCAL;
    }
}
