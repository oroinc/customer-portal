<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller;

use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserACLData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class AclPermissionControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
        $this->loadFixtures([LoadCustomerUserACLData::class]);
    }

    /**
     * @dataProvider accessLevelsDataProvider
     */
    public function testAclAccessLevelsAction(string $oid, int $status, array $expected)
    {
        $this->client->request(
            'GET',
            $this->getUrl('oro_customer_acl_access_levels', ['oid' => $oid])
        );

        $result = $this->client->getResponse();
        $this->assertResponseStatusCodeEquals($result, $status);
        if ($status !== 401) {
            $data = json_decode($result->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $this->assertEquals($expected, $data);
        }
    }

    public function accessLevelsDataProvider(): array
    {
        return [
            'NOT AUTHORISED' => [
                'oid' => '',
                'status' => 401,
                'expected' => [],
            ],
            'AUTHORISED CHECKOUT' => [
                'oid' => 'entity:Oro_Bundle_CheckoutBundle_Entity_Checkout',
                'status' => 200,
                'expected' => ['None', 'User (Own)', 'Department (Same Level)', 'Сorporate (All Levels)'],
            ],
            'AUTHORISED CUSTOMER USER' => [
                'oid' => 'entity:Oro_Bundle_CustomerBundle_Entity_CustomerUser',
                'status' => 200,
                'expected' => ['None', 2 => 'Department (Same Level)', 3 => 'Сorporate (All Levels)'],
            ],
        ];
    }
}
