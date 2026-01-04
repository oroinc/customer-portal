<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

class LoadUserAndGuestWithSameUsername extends LoadCustomerUserData
{
    public const SAME_FIRST_NAME = 'Customer User';
    public const SAME_LAST_NAME = 'Guest';
    public const SAME_EMAIL = 'customer-user-or-guest@example.com';
    public const SAME_PASSWORD = 'test';

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
            'isGuest' => true,
            'password' => self::SAME_PASSWORD,
            'customer' => 'customer.level_1'
        ]
    ];
}
