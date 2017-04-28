<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

class LoadCustomerUsersForImport extends AbstractLoadCustomerUserFixture
{
    const EMAIL = 'example@email.com';

    /**
     * {@inheritdoc}
     */
    protected function getCustomers()
    {
        return [
            [
                'name' => 'default_customer'
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getRoles()
    {
        return ['default_role' => []];
    }

    /**
     * {@inheritdoc}
     */
    protected function getCustomerUsers()
    {
        return [
            [
                'email' => static::EMAIL,
                'customer' => 'default_customer',
                'firstname' => 'Lorem',
                'lastname' => 'Ipsum',
                'role' => 'default_role',
                'password' => 'password'
            ]
        ];
    }
}
