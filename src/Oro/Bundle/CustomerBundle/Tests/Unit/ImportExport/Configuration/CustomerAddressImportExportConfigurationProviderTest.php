<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\ImportExport\Configuration;

use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\ImportExport\Configuration\CustomerAddressImportExportConfigurationProvider;
use Oro\Bundle\ImportExportBundle\Configuration\ImportExportConfiguration;
use Symfony\Contracts\Translation\TranslatorInterface;

class CustomerAddressImportExportConfigurationProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $translator;

    /** @var CustomerAddressImportExportConfigurationProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->expects($this->any())
            ->method('trans')
            ->willReturnArgument(0);

        $this->provider = new CustomerAddressImportExportConfigurationProvider($this->translator);
    }

    public function testGet(): void
    {
        $this->assertEquals(
            new ImportExportConfiguration(
                [
                    ImportExportConfiguration::FIELD_ENTITY_CLASS => CustomerAddress::class,
                    ImportExportConfiguration::FIELD_EXPORT_TEMPLATE_PROCESSOR_ALIAS => 'oro_customer_customer_address',
                    ImportExportConfiguration::FIELD_EXPORT_PROCESSOR_ALIAS => 'oro_customer_customer_address',
                    ImportExportConfiguration::FIELD_IMPORT_PROCESSOR_ALIAS => 'oro_customer_customer_address',
                    ImportExportConfiguration::FIELD_EXPORT_BUTTON_LABEL =>
                        'oro.customer.customeraddress.export.button.label',
                    ImportExportConfiguration::FIELD_IMPORT_ENTITY_LABEL =>
                        'oro.customer.customeraddress.import.entity.label',
                    ImportExportConfiguration::FIELD_IMPORT_JOB_NAME => 'oro_customer_addresses_entity_import_from_csv'
                ]
            ),
            $this->provider->get()
        );
    }
}
