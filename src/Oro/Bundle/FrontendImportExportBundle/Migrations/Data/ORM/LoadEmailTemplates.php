<?php

namespace Oro\Bundle\FrontendImportExportBundle\Migrations\Data\ORM;

use Oro\Bundle\EmailBundle\Migrations\Data\ORM\AbstractHashEmailMigration;
use Oro\Bundle\MigrationBundle\Fixture\VersionedFixtureInterface;

/**
 * Loads email templates.
 */
class LoadEmailTemplates extends AbstractHashEmailMigration implements VersionedFixtureInterface
{
    public function getEmailsDir(): string
    {
        return $this->container
            ->get('kernel')
            ->locateResource('@OroFrontendImportExportBundle/Migrations/Data/ORM/emails');
    }

    public function getVersion(): string
    {
        return '1.1';
    }

    protected function getEmailHashesToUpdate(): array
    {
        return [
            'frontend_export_result_success' => [
                'b57a5a863cf19dd1656b9e2339dfe8fa', // 1.0
            ],
            'frontend_export_result_error' => [
                '46bd2f5b2ada6843db643891563585a2', // 1.0
            ],
        ];
    }
}
