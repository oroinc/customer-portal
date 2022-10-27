<?php

namespace Oro\Bundle\FrontendImportExportBundle\Migrations\Data\ORM;

use Oro\Bundle\EmailBundle\Migrations\Data\ORM\AbstractEmailFixture;

/**
 * Load email template for export result.
 */
class LoadEmailTemplates extends AbstractEmailFixture
{
    /**
     * {@inheritdoc}
     */
    public function getEmailsDir(): string
    {
        return $this->container
            ->get('kernel')
            ->locateResource('@OroFrontendImportExportBundle/Migrations/Data/ORM/emails');
    }
}
