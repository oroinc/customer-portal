<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\ImportExport\Configuration;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\ImportExport\Configuration\CustomerUserAddressImportExportConfigurationProvider;
use Oro\Bundle\ImportExportBundle\Configuration\ImportExportConfiguration;
use Symfony\Contracts\Translation\TranslatorInterface;

class CustomerUserAddressImportExportConfigurationProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $translator;

    /** @var CustomerUserAddressImportExportConfigurationProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->expects($this->any())
            ->method('trans')
            ->willReturnArgument(0);

        $this->provider = new CustomerUserAddressImportExportConfigurationProvider($this->translator);
    }

    public function testGet(): void
    {
        $this->assertEquals(
            new ImportExportConfiguration(
                [
                    ImportExportConfiguration::FIELD_ENTITY_CLASS => CustomerUserAddress::class,
                    ImportExportConfiguration::FIELD_EXPORT_TEMPLATE_PROCESSOR_ALIAS =>
                        'oro_customer_customer_user_address',
                    ImportExportConfiguration::FIELD_EXPORT_PROCESSOR_ALIAS => 'oro_customer_customer_user_address',
                    ImportExportConfiguration::FIELD_IMPORT_PROCESSOR_ALIAS => 'oro_customer_customer_user_address',
                    ImportExportConfiguration::FIELD_EXPORT_BUTTON_LABEL =>
                        'oro.customer.customeruseraddress.export.button.label',
                    ImportExportConfiguration::FIELD_IMPORT_ENTITY_LABEL =>
                        'oro.customer.customeruseraddress.import.entity.label',
                    ImportExportConfiguration::FIELD_IMPORT_JOB_NAME => 'oro_customer_addresses_entity_import_from_csv'
                ]
            ),
            $this->provider->get()
        );
    }
}
