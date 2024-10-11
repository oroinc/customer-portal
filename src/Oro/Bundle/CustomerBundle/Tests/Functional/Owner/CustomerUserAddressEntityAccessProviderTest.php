<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Owner;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Owner\CustomerUserAddressEntityAccessProvider;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserAddressesACLData;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;

class CustomerUserAddressEntityAccessProviderTest extends WebTestCase
{
    private CustomerUserAddressEntityAccessProvider $customerUserAddressEntityAccessProvider;

    protected function setUp(): void
    {
        $this->initClient([], self::generateBasicAuthHeader());
        $this->loadFixtures([LoadCustomerUserAddressesACLData::class, LoadCustomerUserData::class]);
        $this->customerUserAddressEntityAccessProvider = self::getContainer()->get(
            'oro_customer.owner.customer_user_address_entity_access_provider'
        );
    }

    /**
     * @dataProvider getCustomerUserProvider
     */
    public function testIfLoggedCustomerCanCreateUserAddressForAnotherCustomer(
        string $loggedInUserEmail,
        string $customerUserEmail,
        ?string $expectedResult
    ): void {
        $this->createToken(
            $loggedInUserEmail,
            LoadOrganization::ORGANIZATION
        );

        $customerUser = $this->getReference($customerUserEmail);
        $result = $this->customerUserAddressEntityAccessProvider->getCustomerUserAddressIfAllowed($customerUser);

        $this->assertEquals($expectedResult, $this->handleResult($result));
    }

    private function handleResult(?CustomerUserAddress $customerUserAddress): ?string
    {
        return $customerUserAddress ? get_class($customerUserAddress) : null;
    }

    public function getCustomerUserProvider(): array
    {
        return [
            'different customers' => [
                'loggedInUserEmail' => LoadCustomerUserData::LEVEL_1_EMAIL,
                'customerUserEmail' => LoadCustomerUserData::ORPHAN_EMAIL,
                'expectedResult' => null,
            ],
            'customers relation' => [
                'loggedInUserEmail' => LoadCustomerUserData::LEVEL_1_1_EMAIL,
                'customerUserEmail' => LoadCustomerUserData::LEVEL_1_EMAIL,
                'expectedResult' => null,
            ],
            'create for yourself' => [
                'loggedInUserEmail' => LoadCustomerUserData::LEVEL_1_EMAIL,
                'customerUserEmail' => LoadCustomerUserData::LEVEL_1_EMAIL,
                'expectedResult' => CustomerUserAddress::class,
            ],
            'create for department' => [
                'loggedInUserEmail' => LoadCustomerUserData::EMAIL,
                'customerUserEmail' => LoadCustomerUserData::LEVEL_1_EMAIL,
                'expectedResult' => CustomerUserAddress::class,
            ],
        ];
    }

    private function createToken(string $userReference, string $organizationReference): void
    {
        $token = new UsernamePasswordOrganizationToken(
            $this->getReference($userReference),
            'main',
            $this->getReference($organizationReference)
        );

        $this->client->getContainer()->get('security.token_storage')->setToken($token);
    }
}
