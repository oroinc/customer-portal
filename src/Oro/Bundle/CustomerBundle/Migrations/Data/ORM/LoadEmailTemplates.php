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
    public function getEmailsDir(): string
    {
        return $this->container
            ->get('kernel')
            ->locateResource('@OroCustomerBundle/Migrations/Data/ORM/data/emails/customer-user');
    }

    public function getVersion(): string
    {
        return '1.6';
    }

    protected function getEmailHashesToUpdate(): array
    {
        return [
            'customer_user_welcome_email' => [
                'd970bd18538742a4702e70df6f14444d', // 1.0
                '6f2554689920e2d47ac6ea044fdd8e43', // 1.2
                '61e82b3d8c7180e362738a98e266f037', // 1.3
            ],
            'customer_user_welcome_email_registered_by_admin' => [
                'e583b8b7cdea31f8f0ce0a4000b956b9', // 1.1
                'e2a34aa359ce8d958abc7c3eddd7bc93', // 1.3
            ],
            'customer_user_confirmation_email' => [
                '47e012b40cec188ad88dfb7e3379446d', // 1.1
                'e7d7fe65e8b2778b333b5b8f6220ed55', // 1.3
            ],
            'customer_user_reset_password' => [
                '4c987be76cdffc3ade87c9fca27a60be', // 1.1
                '02c65afdfb3e2c61c0c31cd2ff096d0d', // 1.3
                '2d072b726d9f03c3fb0b85357e6c0fca', // 1.4
            ],
            'customer_user_force_reset_password' => [
                'beb25a213aa466f95ae48d710478fa13', // 1.3
                'd9c8afadce0cee68730210c3d50b0d9e'  // 1.4
            ],
        ];
    }
}
