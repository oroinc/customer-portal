<?php

namespace Oro\Bundle\FrontendImportExportBundle\Migrations\Data\ORM;

use Oro\Bundle\EmailBundle\Migrations\Data\ORM\AbstractHashEmailMigration;
use Oro\Bundle\MigrationBundle\Fixture\VersionedFixtureInterface;

/**
 * Loads email templates.
 */
class LoadEmailTemplates extends AbstractHashEmailMigration implements VersionedFixtureInterface
{
    #[\Override]
    public function getEmailsDir(): string
    {
        return $this->container
            ->get('kernel')
            ->locateResource('@OroFrontendImportExportBundle/Migrations/Data/ORM/emails');
    }

    #[\Override]
    public function getVersion(): string
    {
        return '1.2';
    }

    #[\Override]
    protected function getEmailHashesToUpdate(): array
    {
        return [
            'frontend_export_result_success' => [
                'b57a5a863cf19dd1656b9e2339dfe8fa', // 1.0
                '042149b7e24383b8d0974cf9fd7c17c2', // 1.1
                '042149b7e24383b8d0974cf9fd7c17c2', // 1.2
            ],
            'frontend_export_result_error' => [
                '46bd2f5b2ada6843db643891563585a2', // 1.0
                '2dd652478526883fe51697694ccd97d5', // 1.1
                '2dd652478526883fe51697694ccd97d5', // 1.2
            ],
        ];
    }
}
