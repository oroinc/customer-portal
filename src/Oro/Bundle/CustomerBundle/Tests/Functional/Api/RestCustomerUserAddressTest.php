<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api;

use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\AddressBundle\Tests\Functional\DataFixtures\LoadCountryData;
use Oro\Bundle\AddressBundle\Tests\Functional\DataFixtures\LoadRegionData;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Tests\Functional\Api\DataFixtures\LoadCustomerData;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserAddresses;
use Oro\Bundle\UserBundle\DataFixtures\UserUtilityTrait;

/**
 * @group CommunityEdition
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class RestCustomerUserAddressTest extends AbstractRestTest
{
    use UserUtilityTrait;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            LoadCustomerData::class,
            LoadCustomerUserData::class,
            LoadCustomerUserAddresses::class,
            LoadCountryData::class,
            LoadRegionData::class,
        ]);
    }

    public function testGetCustomerUserAddresses()
    {
        $uri = $this->getUrl('oro_rest_api_cget', ['entity' => $this->getEntityType(CustomerUserAddress::class)]);

        $response = $this->request('GET', $uri, []);
        $this->assertApiResponseStatusCodeEquals($response, Response::HTTP_OK, CustomerUserAddress::class, 'get list');
        $content = json_decode($response->getContent(), true);

        $this->assertCount(5, $content['data']);
    }

    public function testCreateCustomerUserAddresses()
    {
        $response = $this->post(
            ['entity' => 'customer_users_addresses'],
            'create_customer_users_address.yml'
        );

        $responseContent = json_decode($response->getContent());
        /** @var CustomerUserAddress */
        $customerUserAddress = $this->getEntityManager()->find(CustomerUserAddress::class, $responseContent->data->id);

        $customer = $this->getReference('customer.1');
        $customerUser = $this->getReference('other.user@test.com');
        $country = $this->getReference('country.usa');
        $region = $this->getReference('region.usny');

        $this->assertEquals('Testname', $customerUserAddress->getFirstName());
        $this->assertEquals('Adrian', $customerUserAddress->getLastName());
        $this->assertEquals('Primary address label', $customerUserAddress->getLabel());
        $this->assertEquals('23400 Caldwell Road', $customerUserAddress->getStreet());
        $this->assertNull($customerUserAddress->getStreet2());
        $this->assertNull($customerUserAddress->getMiddleName());
        $this->assertNull($customerUserAddress->getNamePrefix());
        $this->assertNull($customerUserAddress->getNameSuffix());

        $this->assertEquals(
            $customer->getOwner()->getId(),
            $customerUserAddress->getOwner()->getId()
        );
        $this->assertEquals(
            $customer->getOrganization()->getId(),
            $customerUserAddress->getSystemOrganization()->getId()
        );
        $this->assertEquals(
            $customerUser->getId(),
            $customerUserAddress->getFrontendOwner()->getId()
        );
        $this->assertEquals(
            $country->getIso2Code(),
            $customerUserAddress->getCountryIso2()
        );
        $this->assertEquals(
            $region->getCombinedCode(),
            $customerUserAddress->getRegion()->getCombinedCode()
        );
    }

    public function testUpdateCustomerUserAddresses()
    {
        $repository = $this->getEntityManager()->getRepository(CustomerUserAddress::class);

        $customerUserAddressId = (string)$repository
            ->findOneBy(['street' => LoadCustomerUserAddresses::OTHER_USER_STREET])
            ->getId();

        $response = $this->patch(
            ['entity' => 'customer_users_addresses', 'id' => $customerUserAddressId],
            'update_customer_users_address.yml'
        );
        $responseContent = json_decode($response->getContent());
        $customerUserAddress = $repository->find($responseContent->data->id);

        $this->assertEquals('Testname updated', $customerUserAddress->getFirstName());
        $this->assertEquals('Primary address label updated', $customerUserAddress->getLabel());
        $this->assertEquals(
            $this->getReference('second_customer.user@test.com')->getId(),
            $customerUserAddress->getFrontendOwner()->getId()
        );
        $this->assertEquals(
            $this->getReference('country.mexico')->getIso2Code(),
            $customerUserAddress->getCountryIso2()
        );

        $this->assertResponseContains('update_customer_users_address.yml', $response);
    }
}
