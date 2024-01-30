<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;

class LoadAddressBookUserData extends AbstractLoadCustomerUserFixture
{
    public const USER1 = 'user1';
    public const USER2 = 'user2';
    public const USER3 = 'user3';
    public const USER4 = 'user4';
    public const USER5 = 'user5';

    /** VIEW ACCOUNT ADDRESS */
    public const ROLE1_V_AC_AD = 'role-V_ac_addr';
    /** VIEW ACCOUNT USER ADDRESS */
    public const ROLE2_V_ACU_AD = 'role-V_acu_addr';
    /** VIEW ACCOUNT ADDRESS AND VIEW ACCOUNT USER ADDRESS */
    public const ROLE3_V_AC_AD_V_ACU_AD = 'role-V_ac_addr-V-acu_addr';
    public const ROLE4_NONE = 'role_none';
    /** VIEW/CREATE/EDIT/DELETE ACCOUNT ADDRESS and ACCOUNT USER ADDRESS */
    public const ROLE5_VCED_AC_AD_VCED_AU_AD = 'role-VCED_ac_addr_VCED_au_addr';
    public const ROLE6_VC_AC_AD = 'role-VC_ac_addr';
    public const ROLE7_VC_AU_AD = 'role-VC_acu_addr';

    public const ACCOUNT1 = 'customer1';
    public const ACCOUNT1_USER1 = 'customer1-user1@example.com';
    public const ACCOUNT1_USER2 = 'customer1-user2@example.com';
    public const ACCOUNT1_USER3 = 'customer1-user3@example.com';
    public const ACCOUNT1_USER4 = 'customer1-user4@example.com';
    public const ACCOUNT1_USER5 = 'customer1-user5@example.com';
    public const ACCOUNT1_USER6 = 'customer1-user6@example.com';
    public const ACCOUNT1_USER7 = 'customer1-user7@example.com';

    private array $roles = [
        self::ROLE1_V_AC_AD => [
            [
                'class' => CustomerAddress::class,
                'acls' => ['VIEW_LOCAL'],
            ],
        ],
        self::ROLE2_V_ACU_AD => [
            [
                'class' => CustomerUserAddress::class,
                'acls' => ['VIEW_BASIC'],
            ],
        ],
        self::ROLE3_V_AC_AD_V_ACU_AD => [
            [
                'class' => CustomerUserAddress::class,
                'acls' => ['VIEW_BASIC'],
            ],
            [
                'class' => CustomerAddress::class,
                'acls' => ['VIEW_LOCAL'],
            ],
        ],
        self::ROLE4_NONE => [],
        self::ROLE5_VCED_AC_AD_VCED_AU_AD => [
            [
                'class' => CustomerUserAddress::class,
                'acls' => ['VIEW_BASIC', 'EDIT_BASIC', 'CREATE_BASIC'],
            ],
            [
                'class' => CustomerAddress::class,
                'acls' => ['VIEW_LOCAL'],
            ],
        ],
        self::ROLE6_VC_AC_AD => [
            [
                'class' => CustomerAddress::class,
                'acls' => ['VIEW_LOCAL', 'CREATE_LOCAL'],
            ],
        ],
        self::ROLE7_VC_AU_AD => [
            [
                'class' => CustomerUserAddress::class,
                'acls' => ['VIEW_BASIC', 'CREATE_BASIC'],
            ],
        ]
    ];

    private array $customers = [
        [
            'name' => self::ACCOUNT1,
        ],
    ];

    private array $customerUsers = [
        [
            'email' => self::ACCOUNT1_USER1,
            'firstname' => 'User1FN',
            'lastname' => 'User1LN',
            'password' => self::ACCOUNT1_USER1,
            'customer' => self::ACCOUNT1,
            'role' => self::ROLE1_V_AC_AD,
        ],
        [
            'email' => self::ACCOUNT1_USER2,
            'firstname' => 'User2FN',
            'lastname' => 'User2LN',
            'password' => self::ACCOUNT1_USER2,
            'customer' => self::ACCOUNT1,
            'role' => self::ROLE2_V_ACU_AD,
        ],
        [
            'email' => self::ACCOUNT1_USER3,
            'firstname' => 'User3FN',
            'lastname' => 'User3LN',
            'password' => self::ACCOUNT1_USER3,
            'customer' => self::ACCOUNT1,
            'role' => self::ROLE3_V_AC_AD_V_ACU_AD,
        ],
        [
            'email' => self::ACCOUNT1_USER4,
            'firstname' => 'User3FN',
            'lastname' => 'User3LN',
            'password' => self::ACCOUNT1_USER4,
            'customer' => self::ACCOUNT1,
            'role' => self::ROLE4_NONE,
        ],
        [
            'email' => self::ACCOUNT1_USER5,
            'firstname' => 'User4FN',
            'lastname' => 'User4LN',
            'password' => self::ACCOUNT1_USER5,
            'customer' => self::ACCOUNT1,
            'role' => self::ROLE5_VCED_AC_AD_VCED_AU_AD,
        ],
        [
            'email' => self::ACCOUNT1_USER6,
            'firstname' => 'User4FN',
            'lastname' => 'User4LN',
            'password' => self::ACCOUNT1_USER6,
            'customer' => self::ACCOUNT1,
            'role' => self::ROLE6_VC_AC_AD
        ],
        [
            'email' => self::ACCOUNT1_USER7,
            'firstname' => 'User4FN',
            'lastname' => 'User4LN',
            'password' => self::ACCOUNT1_USER7,
            'customer' => self::ACCOUNT1,
            'role' => self::ROLE7_VC_AU_AD,
        ],
    ];

    /**
     * {@inheritDoc}
     */
    protected function getCustomers(): array
    {
        return $this->customers;
    }

    /**
     * {@inheritDoc}
     */
    protected function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * {@inheritDoc}
     */
    protected function getCustomerUsers(): array
    {
        return $this->customerUsers;
    }
}
