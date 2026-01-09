<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\Configuration;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\ImportExportBundle\Configuration\ImportExportConfiguration;
use Oro\Bundle\ImportExportBundle\Configuration\ImportExportConfigurationInterface;
use Oro\Bundle\ImportExportBundle\Configuration\ImportExportConfigurationProviderInterface;

/**
 * Provides import/export configuration for customer entities.
 *
 * This provider configures the import and export processors for customer entities,
 * specifying the entity class and the processor aliases used for handling customer
 * data during import, export, and template generation operations.
 */
class CustomerImportExportConfigurationProvider implements ImportExportConfigurationProviderInterface
{
    #[\Override]
    public function get(): ImportExportConfigurationInterface
    {
        return new ImportExportConfiguration([
            ImportExportConfiguration::FIELD_ENTITY_CLASS => Customer::class,
            ImportExportConfiguration::FIELD_EXPORT_TEMPLATE_PROCESSOR_ALIAS => 'oro_customer_customer',
            ImportExportConfiguration::FIELD_EXPORT_PROCESSOR_ALIAS => 'oro_customer_customer',
            ImportExportConfiguration::FIELD_IMPORT_PROCESSOR_ALIAS => 'oro_customer_customer',
        ]);
    }
}
