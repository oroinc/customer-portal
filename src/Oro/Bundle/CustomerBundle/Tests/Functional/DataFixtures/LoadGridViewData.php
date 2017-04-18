<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\GridView;
use Oro\Bundle\DataGridBundle\Tests\Functional\DataFixtures\LoadGridViewData as BaseLoadGridViewData;

class LoadGridViewData extends BaseLoadGridViewData
{
    const GRID_VIEW_PRIVATE = 'grid_view.private';
    const GRID_VIEW_PUBLIC = 'grid_view.public';

    /** @var array */
    protected static $data = [
        self::GRID_VIEW_PRIVATE => [
            'name' => 'grid-view-private',
            'type' => GridView::TYPE_PRIVATE,
            'gridName' => 'items-grid',
            'owner' => LoadCustomerUserGridViewACLData::USER_ACCOUNT_2_ROLE_LOCAL,
        ],
        self::GRID_VIEW_PUBLIC => [
            'name' => 'grid-view-public',
            'type' => GridView::TYPE_PUBLIC,
            'gridName' => 'items-grid',
            'owner' => LoadCustomerUserGridViewACLData::USER_ACCOUNT_2_ROLE_LOCAL,
        ],
        self::GRID_VIEW_1 => [
            'name' => 'gridView',
            'type' => GridView::TYPE_PRIVATE,
            'gridName' => 'testing-grid',
            'owner' => LoadCustomerUserGridViewACLData::USER_ACCOUNT_1_ROLE_LOCAL,
        ],
        self::GRID_VIEW_2 => [
            'name' => 'gridView',
            'type' => GridView::TYPE_PUBLIC,
            'gridName' => 'testing-grid',
            'owner' => LoadCustomerUserGridViewACLData::USER_ACCOUNT_2_ROLE_DEEP,
        ],
        self::GRID_VIEW_3 => [
            'name' => 'gridView',
            'type' => GridView::TYPE_PUBLIC,
            'gridName' => 'testing-grid',
            'owner' => LoadCustomerUserGridViewACLData::USER_ACCOUNT_2_ROLE_LOCAL,
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadCustomerUserGridViewACLData::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function createInstance()
    {
        return new GridView();
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserClassName()
    {
        return CustomerUser::class;
    }
}
