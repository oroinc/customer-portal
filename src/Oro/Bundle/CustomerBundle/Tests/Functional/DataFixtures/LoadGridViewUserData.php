<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Oro\Bundle\CustomerBundle\Entity\GridViewUser;
use Oro\Bundle\DataGridBundle\Entity\AbstractGridViewUser;
use Oro\Bundle\DataGridBundle\Tests\Functional\DataFixtures\LoadGridViewUserData as BaseLoadGridViewUserData;

class LoadGridViewUserData extends BaseLoadGridViewUserData
{
    protected static array $data = [
        self::GRID_VIEW_USER_1 => [
            'user' => LoadCustomerUserGridViewACLData::USER_ACCOUNT_1_ROLE_LOCAL,
            'gridView' => LoadGridViewData::GRID_VIEW_1
        ],
        self::GRID_VIEW_USER_2 => [
            'user' => LoadCustomerUserGridViewACLData::USER_ACCOUNT_1_1_ROLE_LOCAL,
            'gridView' => LoadGridViewData::GRID_VIEW_2
        ],
        self::GRID_VIEW_USER_3 => [
            'user' => LoadCustomerUserGridViewACLData::USER_ACCOUNT_2_ROLE_LOCAL,
            'gridView' => LoadGridViewData::GRID_VIEW_3
        ],
        self::GRID_VIEW_USER_4 => [
            'user' => LoadCustomerUserGridViewACLData::USER_ACCOUNT_1_1_ROLE_LOCAL,
            'gridView' => LoadGridViewData::GRID_VIEW_1
        ]
    ];

    /**
     * {@inheritDoc}
     */
    public function getDependencies(): array
    {
        return [LoadCustomerUserGridViewACLData::class, LoadGridViewData::class];
    }

    /**
     * {@inheritDoc}
     */
    protected function createInstance(): AbstractGridViewUser
    {
        return new GridViewUser();
    }
}
