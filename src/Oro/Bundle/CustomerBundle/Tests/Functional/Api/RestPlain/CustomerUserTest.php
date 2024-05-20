<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\RestPlain;

use Oro\Bundle\ApiBundle\Tests\Functional\RestPlainApiTestCase;

class CustomerUserTest extends RestPlainApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures(['@OroCustomerBundle/Tests/Functional/Api/DataFixtures/customer_user_addresses.yml']);
    }

    public function testGet(): void
    {
        $response = $this->get(
            ['entity' => 'customerusers', 'id' => $this->getReference('other.user@test.com')->getId()]
        );

        $this->assertResponseContains(
            [
                'id'        => '@other.user@test.com->id',
                'firstName' => 'First',
                'addresses' => [
                    [
                        'id'    => '@other.user@test.com.address_1->id',
                        'label' => 'other.user@test.com.address_1',
                        'types' => [
                            ['default' => false, 'addressType' => 'billing'],
                            ['default' => true, 'addressType' => 'shipping']
                        ]
                    ],
                    [
                        'id'    => '@other.user@test.com.address_2->id',
                        'label' => 'other.user@test.com.address_2',
                        'types' => []
                    ]
                ]
            ],
            $response
        );
    }
}
