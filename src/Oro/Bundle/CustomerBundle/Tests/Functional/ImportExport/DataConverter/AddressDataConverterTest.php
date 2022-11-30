<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ImportExport\DataConverter;

use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\ImportExport\DataConverter\AddressDataConverter;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class AddressDataConverterTest extends WebTestCase
{
    private AddressDataConverter $dataConverter;

    protected function setUp(): void
    {
        $this->initClient();
        $this->dataConverter = $this->getContainer()->get('oro_customer.importexport.data_converter.customer_address');
        $this->dataConverter->setEntityName(CustomerAddress::class);
    }

    public function testGetBackendHeader()
    {
        $data = $this->dataConverter->convertToExportFormat([]);
        $this->assertEquals(
            [
                'Label',
                'Organization',
                'Name prefix',
                'First name',
                'Middle name',
                'Last name',
                'Name suffix',
                'Street',
                'Street 2',
                'Zip/Postal Code',
                'City',
                'State',
                'State Combined code',
                'Country ISO2 code',
                'Address ID',
                'Phone',
                'Primary',
                'Customer Id',
                'Customer Name',
                'Owner Username',
                'Billing',
                'Default Billing',
                'Shipping',
                'Default Shipping',
                'Delete'
            ],
            array_keys($data)
        );
    }
}
