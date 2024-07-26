<?php

namespace Oro\Bundle\FrontendBundle\Migrations\Data\ORM;

use Oro\Bundle\EmailBundle\Migrations\Data\ORM\AbstractHashEmailMigration;
use Oro\Bundle\MigrationBundle\Fixture\VersionedFixtureInterface;

/**
 * Loads email templates.
 * Loads new templates if not present, updates existing as configured by {@see self::getEmailHashesToUpdate}.
 */
class LoadEmailTemplates extends AbstractHashEmailMigration implements VersionedFixtureInterface
{
    public function getEmailsDir(): string
    {
        return $this->container
            ->get('kernel')
            ->locateResource('@OroFrontendBundle/Migrations/Data/ORM/data/emails');
    }

    public function getVersion(): string
    {
        return '1.3';
    }

    protected function getEmailHashesToUpdate(): array
    {
        return [
            'base_storefront' => [
                '1dee06251e38e88f04080bbe78528901', // 1.0
                '2c9d052ac3cd5790497f2b175886c02a', // 1.1
                'd2bab8193874b960d030057823ddc5ce', // 1.2
                '8bc54a58ba193de9b6a0825d996a59d3', // 1.3
            ],
        ];
    }
}
