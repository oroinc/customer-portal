<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

class LoadCustomerUsersForImport extends AbstractLoadCustomerUserFixture
{
    /**
     * {@inheritdoc}
     */
    protected function getCustomers()
    {
        return [
            ['name' => 'Company A'],
            ['name' => 'Wholesaler B'],
            ['name' => 'Partner C'],
            ['name' => 'Customer G'],
            ['name' => 'Anonymous 1'],
            ['name' => 'Anonymous 2'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getRoles()
    {
        return [
            'Administrator',
            'Buyer',
            'Non-Authenticated Visitors',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getCustomerUsers()
    {
        return [
            [
                'email' => 'AmandaRCole@example.org',
                'customer' => 'Company A',
                'firstname' => 'Amanda',
                'lastname' => 'Cole',
                'role' => 'Administrator',
            ],
            [
                'email' => 'BrandaJSanborn@example.org',
                'customer' => 'Company A',
                'firstname' => 'Branda',
                'lastname' => 'Sanborn',
                'role' => 'Buyer',
            ],
            [
                'email' => 'LoisLLessard@example.org',
                'customer' => 'Company A',
                'firstname' => 'Lois',
                'lastname' => 'Lessard',
                'role' => 'Buyer',
            ],
            [
                'email' => 'LonnieVTownsend@example.org',
                'customer' => 'Company A - East Division',
                'firstname' => 'Lonnie',
                'lastname' => 'Townsend',
                'role' => 'Administrator',
            ],
            [
                'email' => 'JamesSWall@example.org',
                'customer' => 'Company A - East Division',
                'firstname' => 'James',
                'lastname' => 'Wall',
                'role' => 'Buyer',
            ],
            [
                'email' => 'RuthWMaxwell@example.org',
                'customer' => 'Company A - West Division',
                'firstname' => 'Ruth',
                'lastname' => 'Maxwell',
                'role' => 'Administrator',
            ],
            [
                'email' => 'BrentLJohnson@example.org',
                'customer' => 'Company A - West Division',
                'firstname' => 'Brent',
                'lastname' => 'Johnson',
                'role' => 'Buyer',
            ],
            [
                'email' => 'NancyJSallee@example.com',
                'customer' => 'Wholesaler B',
                'firstname' => 'Nancy',
                'lastname' => 'Sallee',
                'role' => 'Administrator',
            ],
            [
                'email' => 'MarleneSBradley@example.com',
                'customer' => 'Wholesaler B',
                'firstname' => 'Marlene',
                'lastname' => 'Bradley',
                'role' => 'Buyer',
            ],
            [
                'email' => 'JuanaPBrzezinski@example.net',
                'customer' => 'Partner C',
                'firstname' => 'Juana',
                'lastname' => 'Brzezinski',
                'role' => 'Administrator',
            ],
            [
                'email' => 'IsabelMartin@example.org',
                'customer' => 'Anonymous 1',
                'firstname' => 'Isabel',
                'lastname' => 'Martin',
                'role' => 'Non-Authenticated Visitors',
            ],
            [
                'email' => 'ElbertMoore@example.org',
                'customer' => 'Anonymous 2',
                'firstname' => 'Elbert',
                'lastname' => 'Moore',
                'role' => 'Non-Authenticated Visitors',
            ],
        ];
    }
}
