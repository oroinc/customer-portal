<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ControllerFrontend;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserRoleACLData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class FrontendAutocompleteControllerTest extends WebTestCase
{
    private const CUSTOMER_SEARCH_HANDLER = 'oro_customer_customer';

    #[\Override]
    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadCustomerUserRoleACLData::class]);
    }

    public function testBackendSearchHandlerIsNotAvailableForCustomerVisitor(): void
    {
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_frontend_autocomplete_search',
                ['name' => 'assigned_to_organization_users']
            ),
            [],
            [],
            ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']
        );

        $response = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true);

        self::assertFalse($response->isSuccessful());
        self::assertEmpty($responseData['results'] ?? []);
    }

    public function testCustomerSearchIsNotAvailableForCustomerVisitor(): void
    {
        $responseData = $this->requestCustomerAutocomplete();

        self::assertSame(
            [
                'results' => [],
                'hasMore' => false,
                'errors' => ['Access denied.'],
            ],
            $responseData
        );
    }

    public function testCustomerSearchIsAvailableForLoggedInCustomerUser(): void
    {
        $this->loginUser(LoadCustomerUserRoleACLData::USER_ACCOUNT_1_ROLE_LOCAL);

        /** @var Customer $customer */
        $customer = $this->getReference('customer.level_1.1');

        self::assertSame(
            [
                'results' => [
                    [
                        'id' => $customer->getId(),
                        'name' => $customer->getName(),
                    ],
                ],
                'more' => false,
            ],
            $this->requestCustomerAutocomplete($customer->getId())
        );
    }

    private function requestCustomerAutocomplete(?int $customerId = null): array
    {
        $parameters = ['name' => self::CUSTOMER_SEARCH_HANDLER];
        if (null !== $customerId) {
            $parameters['query'] = (string)$customerId;
            $parameters['search_by_id'] = true;
        }

        $this->client->request(
            'GET',
            $this->getUrl('oro_frontend_autocomplete_search', $parameters),
            [],
            [],
            ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']
        );

        $response = $this->client->getResponse();
        self::assertJsonResponseStatusCodeEquals($response, Response::HTTP_OK);

        return self::jsonToArray($response->getContent());
    }
}
