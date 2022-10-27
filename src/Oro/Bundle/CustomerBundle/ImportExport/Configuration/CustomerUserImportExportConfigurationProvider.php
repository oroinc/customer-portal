<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\Configuration;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\ImportExportBundle\Configuration\ImportExportConfiguration;
use Oro\Bundle\ImportExportBundle\Configuration\ImportExportConfigurationInterface;
use Oro\Bundle\ImportExportBundle\Configuration\ImportExportConfigurationProviderInterface;

/**
 * Import-Export configuration for Customer User.
 */
class CustomerUserImportExportConfigurationProvider implements ImportExportConfigurationProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function get(): ImportExportConfigurationInterface
    {
        return new ImportExportConfiguration([
            ImportExportConfiguration::FIELD_ENTITY_CLASS => CustomerUser::class,
            ImportExportConfiguration::FIELD_EXPORT_PROCESSOR_ALIAS => 'oro_customer_customer_user',
            ImportExportConfiguration::FIELD_EXPORT_TEMPLATE_PROCESSOR_ALIAS => 'oro_customer_customer_user',
            ImportExportConfiguration::FIELD_IMPORT_PROCESSOR_ALIAS => 'oro_customer_customer_user',
            ImportExportConfiguration::FIELD_IMPORT_JOB_NAME => 'oro_customer_user_entity_import_from_csv'
        ]);
    }
}
