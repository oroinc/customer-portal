<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ApiFrontend\RestJsonApi;

use Oro\Bundle\CustomerBundle\Tests\Functional\ApiFrontend\DataFixtures\LoadCustomerData;
use Oro\Bundle\FrontendBundle\Tests\Functional\ApiFrontend\FrontendRestJsonApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class CustomerAddressForVisitorTest extends FrontendRestJsonApiTestCase
{
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->initializeVisitor();
        $this->loadFixtures([
            LoadCustomerData::class,
            '@OroCustomerBundle/Tests/Functional/ApiFrontend/DataFixtures/customer_address.yml'
        ]);
    }

    public function testTryToGetList()
    {
        $response = $this->cget(
            ['entity' => 'customeraddresses'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);
    }

    public function testTryToGet()
    {
        $response = $this->get(
            ['entity' => 'customeraddresses', 'id' => '<toString(@customer_address1->id)>'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);
    }

    public function testTryToCreate()
    {
        $response = $this->post(
            ['entity' => 'customeraddresses'],
            'create_customer_address_min.yml',
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);
    }

    public function testTryToUpdate()
    {
        $addressId = $this->getReference('customer_address1')->getId();

        $response = $this->patch(
            ['entity' => 'customeraddresses', 'id' => (string)$addressId],
            [
                'data' => [
                    'type'       => 'customeraddresses',
                    'id'         => (string)$addressId,
                    'attributes' => [
                        'label' => 'Updated Address'
                    ]
                ]
            ],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);
    }

    public function testTryToDelete()
    {
        $addressId = $this->getReference('customer_address1')->getId();

        $response = $this->delete(
            ['entity' => 'customeraddresses', 'id' => (string)$addressId],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);
    }

    public function testTryToDeleteList()
    {
        $addressId = $this->getReference('customer_address1')->getId();

        $response = $this->cdelete(
            ['entity' => 'customeraddresses'],
            ['filter' => ['id' => (string)$addressId]],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);
    }
}
