<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\GridViewUser;
use Oro\Bundle\DataGridBundle\Tests\Functional\DataFixtures\LoadGridViewUserData as BaseLoadGridViewUserData;

class LoadGridViewUserData extends BaseLoadGridViewUserData
{
    /** @var array */
    protected static $data = [
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
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [LoadGridViewData::class];
    }

    /**
     * {@inheritdoc}
     */
    protected function createInstance()
    {
        return new GridViewUser();
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserClassName()
    {
        return CustomerUser::class;
    }
}
