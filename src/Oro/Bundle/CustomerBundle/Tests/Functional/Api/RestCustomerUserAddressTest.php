<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api;

use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\AddressBundle\Tests\Functional\DataFixtures\LoadCountryData;
use Oro\Bundle\AddressBundle\Tests\Functional\DataFixtures\LoadRegionData;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
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

    public function testGetCustomerUserAddress()
    {
        $repository = $this->getEntityManager()->getRepository(CustomerUserAddress::class);
        $customerUserAddressId = (string)$repository
            ->findOneBy(['label' => LoadCustomerUserAddresses::OTHER_USER_LABEL])
            ->getId();

        $uri = $this->getUrl('oro_rest_api_get', [
            'entity' => $this->getEntityType(CustomerUserAddress::class),
            'id' => $customerUserAddressId,
        ]);

        $response = $this->request('GET', $uri, []);
        $this->assertApiResponseStatusCodeEquals(
            $response,
            Response::HTTP_OK,
            CustomerUserAddress::class,
            'get list'
        );
        $content = json_decode($response->getContent(), true);

        // there are only 5 fixtures added from LoadCustomerUserAddresses
        $this->assertMatchesLastFixture($content['data']);
    }

    public function testGetInexistingCustomerUserAddress()
    {
        $uri = $this->getUrl('oro_rest_api_get', [
            'entity' => $this->getEntityType(CustomerUserAddress::class),
            'id' => '99999999999',
        ]);

        $response = $this->request('GET', $uri, []);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testGetCustomerUserAddresses()
    {
        $uri = $this->getUrl('oro_rest_api_cget', [
            'entity' => $this->getEntityType(CustomerUserAddress::class)
        ]);

        $response = $this->request('GET', $uri, [
            'sort' => '-id'
        ]);

        $this->assertApiResponseStatusCodeEquals(
            $response,
            Response::HTTP_OK,
            CustomerUserAddress::class,
            'get list'
        );
        $content = json_decode($response->getContent(), true);

        // there are only 5 fixtures added from LoadCustomerUserAddresses
        $this->assertCount(5, $content['data']);
        $this->assertMatchesLastFixture(reset($content['data']));
    }

    public function testCreateCustomerUserAddresses()
    {
        $response = $this->post(
            ['entity' => $this->getEntityType(CustomerUserAddress::class)],
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

    public function testUpdateWrongCustomerUserAddresses()
    {
        $uri = $this->getUrl('oro_rest_api_patch', [
            'entity' => $this->getEntityType(CustomerUserAddress::class),
            'id' => '99999999999',
        ]);

        $response = $this->request(
            'PATCH',
            $uri,
            $this->getRequestData('update_customer_users_address.yml')
        );

        $this->assertEquals(Response::HTTP_CONFLICT, $response->getStatusCode());
    }

    public function testUpdateCustomerUserAddresses()
    {
        $repository = $this->getEntityManager()->getRepository(CustomerUserAddress::class);

        $customerUserAddressId = (string)$repository
            ->findOneBy(['label' => LoadCustomerUserAddresses::OTHER_USER_LABEL])
            ->getId();

        $response = $this->patch([
            'entity' => $this->getEntityType(CustomerUserAddress::class),
            'id' => $customerUserAddressId],
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

    public function testDeleteCustomerUserAddresses()
    {
        $repository = $this->getEntityManager()->getRepository(CustomerUserAddress::class);
        $customerUserAddressId = (string)$repository->findOneBy([])->getId();

        // make the delete request
        $uri = $this->getUrl(
            'oro_rest_api_delete',
            [
                'entity' => $this->getEntityType(CustomerUserAddress::class),
                'id' => $customerUserAddressId,
            ]
        );

        // check response confirms deletion
        $response = $this->request('DELETE', $uri);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $uri = $this->getUrl(
            'oro_rest_api_get',
            [
                'entity' => $this->getEntityType(CustomerUserAddress::class),
                'id' => $customerUserAddressId
            ]
        );

        // verify it's not available anymore for GET requests
        $response = $this->request('GET', $uri, []);
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    /**
     * @param array $customerUserAddressData
     */
    private function assertMatchesLastFixture($customerUserAddressData)
    {
        $attributes = $customerUserAddressData['attributes'];
        $this->assertNull($attributes['phone']);
        $this->assertTrue($attributes['primary']);
        $this->assertEquals('other.user@test.com.address_1', $attributes['label']);
        $this->assertEquals('2849 Junkins Avenue', $attributes['street']);
        $this->assertNull($attributes['street2']);
        $this->assertEquals('Albany', $attributes['city']);
        $this->assertEquals('31707', $attributes['postalCode']);
        $this->assertEquals('Test Org', $attributes['organization']);
        $this->assertNull($attributes['namePrefix']);
        $this->assertNull($attributes['firstName']);
        $this->assertNull($attributes['middleName']);
        $this->assertNull($attributes['nameSuffix']);
        $this->assertNotNull($attributes['created']);
        $this->assertNotNull($attributes['updated']);

        $customerUserRepository = $this->getEntityManager()->getRepository(CustomerUser::class);
        $customerUser = $customerUserRepository->findOneBy(['email' => 'other.user@test.com']);

        $relationships = $customerUserAddressData['relationships'];
        $this->assertEquals('US', $relationships['country']['data']['id']);
        $this->assertEquals('US-GA', $relationships['region']['data']['id']);
        $this->assertEquals(
            $customerUser->getId(),
            $relationships['frontendOwner']['data']['id']
        );
    }
}
