<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ImportExport\DataConverter;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\ImportExport\DataConverter\CustomerUserAddressDataConverter;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CustomerUserAddressDataConverterTest extends WebTestCase
{
    private CustomerUserAddressDataConverter $dataConverter;

    protected function setUp(): void
    {
        $this->initClient();
        $this->dataConverter = $this->getContainer()
            ->get('oro_customer.importexport.data_converter.customer_user_address');
        $this->dataConverter->setEntityName(CustomerUserAddress::class);
    }

    public function testGetBackendHeader()
    {
        $data = $this->dataConverter->convertToExportFormat([]);
        $this->assertEquals(
            [
                'Label',
                'Organization',
                'Name Prefix',
                'First Name',
                'Middle Name',
                'Last Name',
                'Name Suffix',
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
                'Email Address',
                'Owner Username',
                'Billing',
                'Default Billing',
                'Shipping',
                'Default Shipping',
                'Delete',
                'Customer User ID'
            ],
            array_keys($data)
        );
    }
}
