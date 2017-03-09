<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Action;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserAddresses;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CustomerUserAddressActionTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader(LoadCustomerUserData::EMAIL, LoadCustomerUserData::PASSWORD)
        );
        $this->client->useHashNavigation(true);
        $this->loadFixtures(
            [
                LoadCustomerUserData::class,
                LoadCustomerUserAddresses::class
            ]
        );
    }

    public function testDelete()
    {
        /** @var CustomerUser customerUser */
        $customerUser = $this->getReference(LoadCustomerUserData::EMAIL);
        $id = $customerUser->getAddresses()->first()->getId();
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_frontend_action_operation_execute',
                [
                    'operationName' => 'oro_customer_user_frontend_address_delete',
                    'entityId' => $id,
                    'entityClass' => CustomerUserAddress::class,
                ]
            ),
            [],
            [],
            ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']
        );

        $this->assertJsonResponseStatusCodeEquals($this->client->getResponse(), 200);

        static::getContainer()->get('doctrine')->getManagerForClass(CustomerUserAddress::class)->clear();

        $removedAddress = static::getContainer()
            ->get('doctrine')
            ->getRepository('OroCustomerBundle:CustomerUserAddress')
            ->find($id);

        static::assertNull($removedAddress);
    }
}
