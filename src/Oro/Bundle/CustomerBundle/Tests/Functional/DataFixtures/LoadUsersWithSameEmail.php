<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

class LoadUsersWithSameEmail extends LoadCustomerUserData
{
    const SAME_FIRST_NAME = 'Customer User';
    const SAME_LAST_NAME = 'Guest';
    const SAME_EMAIL = 'customer-user-or-guest@example.com';
    const SAME_PASSWORD = 'test';

    /**
     * @var array
     */
    protected static $users = [
        [
            'first_name' => self::SAME_FIRST_NAME,
            'last_name' => self::SAME_LAST_NAME,
            'email' => self::SAME_EMAIL,
            'enabled' => true,
            'isGuest' => false,
            'confirmed' => false,
            'confirmationToken' => 'confirmation_token',
            'password' => self::SAME_PASSWORD,
            'customer' => 'customer.level_1'
        ],
        [
            'first_name' => self::SAME_FIRST_NAME,
            'last_name' => self::SAME_LAST_NAME,
            'email' => self::SAME_EMAIL,
            'enabled' => false,
            'confirmed' => false,
            'isGuest' => false,
            'password' => self::SAME_PASSWORD,
            'customer' => 'customer.level_1'
        ]
    ];
}
