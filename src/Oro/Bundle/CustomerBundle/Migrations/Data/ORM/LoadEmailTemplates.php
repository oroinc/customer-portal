<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\ORM;

use Oro\Bundle\EmailBundle\Migrations\Data\ORM\AbstractHashEmailMigration;
use Oro\Bundle\MigrationBundle\Fixture\VersionedFixtureInterface;

/**
 * Load email templates for Customer User entity.
 * Load new templates if not present, update existing as configured by {@see self::getEmailHashesToUpdate}.
 */
class LoadEmailTemplates extends AbstractHashEmailMigration implements VersionedFixtureInterface
{
    #[\Override]
    public function getEmailsDir(): string
    {
        return $this->container
            ->get('kernel')
            ->locateResource('@OroCustomerBundle/Migrations/Data/ORM/data/emails/customer-user');
    }

    #[\Override]
    public function getVersion(): string
    {
        return '7.0.5.0';
    }

    #[\Override]
    protected function getEmailHashesToUpdate(): array
    {
        return [
            'customer_user_welcome_email' => [
                'd970bd18538742a4702e70df6f14444d', // 1.0
                '6f2554689920e2d47ac6ea044fdd8e43', // 1.2
                '61e82b3d8c7180e362738a98e266f037', // 1.3
                '54a5dee6405f3d9e1660fe0f3dcb21c1', // 1.5
                '54a5dee6405f3d9e1660fe0f3dcb21c1', // 1.6
                '54a5dee6405f3d9e1660fe0f3dcb21c1', // 1.7
                '54a5dee6405f3d9e1660fe0f3dcb21c1', // 1.8
            ],
            'customer_user_welcome_email_registered_by_admin' => [
                'e583b8b7cdea31f8f0ce0a4000b956b9', // 1.1
                'e2a34aa359ce8d958abc7c3eddd7bc93', // 1.3
                'a36f8bfd13af225f6fb5a1c79397bb4f', // 1.5
                'a36f8bfd13af225f6fb5a1c79397bb4f', // 1.6
                'a36f8bfd13af225f6fb5a1c79397bb4f', // 1.7
                '0a24f26d8cd2c15fb396d6bd34e99437', // 1.8
                '57ae2ce533a0fee9e6b5b7ae8c83864e', // 1.9
                '1bd5b96da9dde8b95d67cc5534eb6c79', // 7.0.5.0
            ],
            'customer_user_confirmation_email' => [
                '47e012b40cec188ad88dfb7e3379446d', // 1.1
                'e7d7fe65e8b2778b333b5b8f6220ed55', // 1.3
                'cbe6cc8b395a5aa7d0642220b1cacfd9', // 1.5
                'cbe6cc8b395a5aa7d0642220b1cacfd9', // 1.6
                'cbe6cc8b395a5aa7d0642220b1cacfd9', // 1.7
            ],
            'customer_user_reset_password' => [
                '4c987be76cdffc3ade87c9fca27a60be', // 1.1
                '02c65afdfb3e2c61c0c31cd2ff096d0d', // 1.3
                '2d072b726d9f03c3fb0b85357e6c0fca', // 1.4
                'a03885915c75cb0ead5b4e8dc21c457e', // 1.5
                'a03885915c75cb0ead5b4e8dc21c457e', // 1.6
                'a03885915c75cb0ead5b4e8dc21c457e', // 1.7
                '1a2357527186681b32d9e5a6513c40ae', // 7.0.5.0
            ],
            'customer_user_force_reset_password' => [
                'beb25a213aa466f95ae48d710478fa13', // 1.3
                'd9c8afadce0cee68730210c3d50b0d9e', // 1.4
                '52324c35721f05c73b6c5509633c7908', // 1.5
                '52324c35721f05c73b6c5509633c7908', // 1.6
                '52324c35721f05c73b6c5509633c7908', // 1.7
                '45c84ee889df90521224a212d892e9bb', // 1.9
                '772a15aa4382fb967b60a7c47c5719cd', // 7.0.5.0
            ],
            'customer_user_email_change_verification_to_old_email' => [
                'f477160ba58f2bb45eb30a2114d16dc9', // 1.7
                '4e14e5f94e75a58d2eacdaa423be807b', // 1.8
                'bd024c44a4f1a3e14f6cb6ccdb8937e2', // 7.0.5.0
            ],
            'customer_user_email_change_confirmation' => [
                '44d604e21af4dd03913f7860e9a6b441', // 1.7
                '250896ec2d754259e813db39ea5a0cb3', // 1.8
                '3d0ef2db5b64f98e462935107e0ec7e4', // 7.0.5.0
            ],
            'customer_user_email_change_verification_to_new_email' => [
                '43e8ff40dce339110d8a369238ec5820', // 1.7
            ]
        ];
    }
}
