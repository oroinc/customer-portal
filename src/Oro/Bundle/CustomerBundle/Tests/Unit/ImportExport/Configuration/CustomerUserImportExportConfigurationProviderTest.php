<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\ImportExport\Configuration;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\ImportExport\Configuration\CustomerUserImportExportConfigurationProvider;
use Oro\Bundle\ImportExportBundle\Configuration\ImportExportConfiguration;
use PHPUnit\Framework\TestCase;

class CustomerUserImportExportConfigurationProviderTest extends TestCase
{
    public function testGet()
    {
        self::assertEquals(
            new ImportExportConfiguration([
                ImportExportConfiguration::FIELD_ENTITY_CLASS => CustomerUser::class,
                ImportExportConfiguration::FIELD_EXPORT_PROCESSOR_ALIAS => 'oro_customer_customer_user',
                ImportExportConfiguration::FIELD_EXPORT_TEMPLATE_PROCESSOR_ALIAS => 'oro_customer_customer_user',
                ImportExportConfiguration::FIELD_IMPORT_PROCESSOR_ALIAS => 'oro_customer_customer_user',
                ImportExportConfiguration::FIELD_IMPORT_JOB_NAME => 'oro_customer_user_entity_import_from_csv'
            ]),
            (new CustomerUserImportExportConfigurationProvider())->get()
        );
    }
}
