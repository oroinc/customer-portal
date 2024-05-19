<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\RestPlain;

use Oro\Bundle\ApiBundle\Tests\Functional\RestPlainApiTestCase;

class CustomerTest extends RestPlainApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures(['@OroCustomerBundle/Tests/Functional/Api/DataFixtures/customer_addresses.yml']);
    }

    public function testGet(): void
    {
        $response = $this->get(
            ['entity' => 'customers', 'id' => $this->getReference('customer.level_1.1')->getId()]
        );

        $this->assertResponseContains(
            [
                'id'        => '@customer.level_1.1->id',
                'name'      => 'customer.level_1.1',
                'addresses' => [
                    [
                        'id'    => '@customer.level_1.1.address_1->id',
                        'label' => 'customer.level_1.1.address_1',
                        'types' => [
                            ['default' => false, 'addressType' => 'billing'],
                            ['default' => true, 'addressType' => 'shipping']
                        ]
                    ],
                    [
                        'id'    => '@customer.level_1.1.address_2->id',
                        'label' => 'customer.level_1.1.address_2',
                        'types' => []
                    ]
                ]
            ],
            $response
        );
    }
}
