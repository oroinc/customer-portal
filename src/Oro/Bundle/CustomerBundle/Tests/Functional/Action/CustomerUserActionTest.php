<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Action;

use Oro\Bundle\ActionBundle\Tests\Functional\OperationAwareTestTrait;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserACLData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CustomerUserActionTest extends WebTestCase
{
    use OperationAwareTestTrait;

    protected function setUp(): void
    {
        $this->initClient();
        $this->client->useHashNavigation(true);
        $this->loadFixtures([LoadCustomerUserACLData::class]);
    }

    /**
     * @dataProvider deleteDataProvider
     */
    public function testDelete(string $login, string $resource, int $status)
    {
        $this->loginUser($login);
        $id = $this->getReference($resource)->getId();

        $operationName = 'oro_customer_frontend_user_delete';
        $entityClass = CustomerUser::class;
        $this->client->request(
            'POST',
            $this->getUrl(
                'oro_frontend_action_operation_execute',
                [
                    'operationName' => $operationName,
                    'entityId' => $id,
                    'entityClass' => $entityClass,
                ]
            ),
            $this->getOperationExecuteParams($operationName, $id, $entityClass),
            [],
            ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']
        );

        $this->assertJsonResponseStatusCodeEquals($this->client->getResponse(), $status);

        if ($status === 200) {
            self::getContainer()->get('doctrine')->getManagerForClass($entityClass)->clear();

            $removedCustomer = self::getContainer()->get('doctrine')->getRepository(CustomerUser::class)
                ->find($id);

            self::assertNull($removedCustomer);
        }
    }

    public function deleteDataProvider(): array
    {
        return [
            'anonymous user' => [
                'login' => '',
                'resource' => LoadCustomerUserACLData::USER_ACCOUNT_1_1_ROLE_LOCAL,
                'status' => 403,
            ],
            'same customer: LOCAL_VIEW_ONLY' => [
                'login' => LoadCustomerUserACLData::USER_ACCOUNT_1_ROLE_LOCAL_VIEW_ONLY,
                'resource' => LoadCustomerUserACLData::USER_ACCOUNT_1_ROLE_LOCAL,
                'status' => 403,
            ],
            'parent customer: LOCAL' => [
                'login' => LoadCustomerUserACLData::USER_ACCOUNT_1_ROLE_LOCAL,
                'resource' => LoadCustomerUserACLData::USER_ACCOUNT_1_1_ROLE_LOCAL,
                'status' => 403,
            ],
            'parent customer: DEEP_VIEW_ONLY' => [
                'login' => LoadCustomerUserACLData::USER_ACCOUNT_1_ROLE_DEEP_VIEW_ONLY,
                'resource' => LoadCustomerUserACLData::USER_ACCOUNT_1_1_ROLE_LOCAL,
                'status' => 403,
            ],
            'parent customer: DEEP' => [
                'login' => LoadCustomerUserACLData::USER_ACCOUNT_1_ROLE_DEEP,
                'resource' => LoadCustomerUserACLData::USER_ACCOUNT_1_1_ROLE_LOCAL,
                'status' => 200,
            ],
            'same customer: LOCAL' => [
                'login' => LoadCustomerUserACLData::USER_ACCOUNT_1_ROLE_LOCAL,
                'resource' => LoadCustomerUserACLData::USER_ACCOUNT_1_ROLE_DEEP_VIEW_ONLY,
                'status' => 200,
            ],
        ];
    }
}
