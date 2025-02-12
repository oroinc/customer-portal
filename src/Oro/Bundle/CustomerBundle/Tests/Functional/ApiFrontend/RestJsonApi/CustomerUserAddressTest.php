<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ApiFrontend\RestJsonApi;

use Oro\Bundle\AddressBundle\Tests\Functional\Api\RestJsonApi\AddressCountryAndRegionTestTrait;
use Oro\Bundle\AddressBundle\Tests\Functional\Api\RestJsonApi\PrimaryAddressTestTrait;
use Oro\Bundle\AddressBundle\Tests\Functional\Api\RestJsonApi\UnchangeableAddressOwnerTestTrait;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Tests\Functional\Api\RestJsonApi\AddressTypeTestTrait;
use Oro\Bundle\CustomerBundle\Tests\Functional\ApiFrontend\DataFixtures\LoadAdminCustomerUserData;
use Oro\Bundle\FrontendBundle\Tests\Functional\ApiFrontend\FrontendRestJsonApiTestCase;

/**
 * @dbIsolationPerTest
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class CustomerUserAddressTest extends FrontendRestJsonApiTestCase
{
    use AddressCountryAndRegionTestTrait;
    use PrimaryAddressTestTrait;
    use UnchangeableAddressOwnerTestTrait;
    use AddressTypeTestTrait;

    private const ENTITY_CLASS                     = CustomerUserAddress::class;
    private const ENTITY_TYPE                      = 'customeruseraddresses';
    private const OWNER_ENTITY_TYPE                = 'customerusers';
    private const OWNER_RELATIONSHIP               = 'customerUser';
    private const CREATE_MIN_REQUEST_DATA          = 'create_customer_user_address_min.yml';
    private const OWNER_CREATE_MIN_REQUEST_DATA    = 'create_customer_user_min.yml';
    private const IS_REGION_REQUIRED               = true;
    private const COUNTRY_REGION_ADDRESS_REF       = 'another_customer_user_address1';
    private const PRIMARY_ADDRESS_REF              = 'another_customer_user_address1';
    private const DEFAULT_ADDRESS_REF              = 'customer_user_address1';
    private const BILLING_ADDRESS_REF              = 'customer_user_address3';
    private const BILLING_AND_SHIPPING_ADDRESS_REF = 'customer_user_address1';
    private const UNCHANGEABLE_ADDRESS_REF         = 'customer_user_address1';
    private const OWNER_REF                        = 'customer_user1';
    private const ANOTHER_OWNER_REF                = 'another_customer_user';
    private const ANOTHER_OWNER_ADDRESS_2_REF      = 'another_customer_user_address2';

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
            LoadAdminCustomerUserData::class,
            '@OroCustomerBundle/Tests/Functional/ApiFrontend/DataFixtures/customer_user_address.yml'
        ]);
    }

    private function getOwner(CustomerUserAddress $address): ?CustomerUser
    {
        return $address->getFrontendOwner();
    }

    public function testGetList(): void
    {
        $response = $this->cget(
            ['entity' => 'customeruseraddresses']
        );

        $this->assertResponseContains('cget_customer_user_address.yml', $response);
    }

    public function testGetListFilterByAddressType(): void
    {
        $response = $this->cget(
            ['entity' => 'customeruseraddresses'],
            ['filter' => ['addressType' => 'billing']]
        );

        $this->assertResponseContains(
            [
                'data' => [
                    [
                        'type' => self::ENTITY_TYPE,
                        'id'   => '<toString(@customer_user_address1->id)>'
                    ],
                    [
                        'type' => self::ENTITY_TYPE,
                        'id'   => '<toString(@customer_user_address3->id)>'
                    ]
                ]
            ],
            $response
        );
    }

    public function testTryToGetListFilterByTypes(): void
    {
        $response = $this->cget(
            ['entity' => 'customeruseraddresses'],
            ['filter' => ['types' => 'billing']],
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'filter constraint',
                'detail' => 'The filter is not supported.',
                'source' => ['parameter' => 'filter[types]']
            ],
            $response
        );
    }

    public function testGet(): void
    {
        $response = $this->get(
            ['entity' => 'customeruseraddresses', 'id' => '<toString(@another_customer_user_address1->id)>']
        );

        $this->assertResponseContains('get_customer_user_address.yml', $response);
    }

    public function testCreate(): void
    {
        $customer = $this->getReference('customer');
        $ownerId = $customer->getOwner()->getId();
        $organizationId = $customer->getOrganization()->getId();
        $customerUserId = $this->getReference('another_customer_user')->getId();
        $countryId = $this->getReference('country_usa')->getIso2Code();
        $regionId = $this->getReference('region_usa_california')->getCombinedCode();

        $response = $this->post(
            ['entity' => 'customeruseraddresses'],
            'create_customer_user_address.yml'
        );

        $addressId = (int)$this->getResourceId($response);
        $responseContent = $this->updateResponseContent('create_customer_user_address.yml', $response);
        $this->assertResponseContains($responseContent, $response);

        /** @var CustomerUserAddress $address */
        $address = $this->getEntityManager()
            ->find(CustomerUserAddress::class, $addressId);
        self::assertNotNull($address);
        self::assertEquals('New Address', $address->getLabel());
        self::assertTrue($address->isPrimary());
        self::assertEquals('123-456', $address->getPhone());
        self::assertEquals('Acme', $address->getOrganization());
        self::assertEquals('Street 1', $address->getStreet());
        self::assertEquals('Street 2', $address->getStreet2());
        self::assertEquals('Los Angeles', $address->getCity());
        self::assertEquals('90001', $address->getPostalCode());
        self::assertEquals('Mr.', $address->getNamePrefix());
        self::assertEquals('M.D.', $address->getNameSuffix());
        self::assertEquals('John', $address->getFirstName());
        self::assertEquals('Edgar', $address->getMiddleName());
        self::assertEquals('Doo', $address->getLastName());
        self::assertCount(2, $address->getAddressTypes());
        foreach ($address->getAddressTypes() as $type) {
            if ('billing' === $type->getType()->getName()) {
                self::assertTrue($type->isDefault(), 'billing default');
            } else {
                self::assertEquals('shipping', $type->getType()->getName(), 'shipping type');
                self::assertFalse($type->isDefault(), 'shipping default');
            }
        }
        self::assertEquals($organizationId, $address->getSystemOrganization()->getId());
        self::assertEquals($ownerId, $address->getOwner()->getId());
        self::assertEquals($customerUserId, $address->getFrontendOwner()->getId());
        self::assertEquals($countryId, $address->getCountry()->getIso2Code());
        self::assertEquals($regionId, $address->getRegion()->getCombinedCode());
        self::assertEquals('2024-10-11 00:00:00', $address->getValidatedAt()->format('Y-m-d H:i:s'));
    }

    public function testTryToCreateWithRequiredDataOnlyAndWithoutOrganizationAndFirstNameAndLastName(): void
    {
        $data = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        unset($data['data']['attributes']['organization']);
        $response = $this->post(
            ['entity' => 'customeruseraddresses'],
            $data,
            [],
            false
        );

        $this->assertResponseValidationErrors(
            [
                [
                    'title'  => 'name or organization constraint',
                    'detail' => 'Organization or First Name and Last Name should not be blank.',
                    'source' => ['pointer' => '/data/attributes/organization']
                ],
                [
                    'title'  => 'name or organization constraint',
                    'detail' => 'First Name and Last Name or Organization should not be blank.',
                    'source' => ['pointer' => '/data/attributes/firstName']
                ],
                [
                    'title'  => 'name or organization constraint',
                    'detail' => 'Last Name and First Name or Organization should not be blank.',
                    'source' => ['pointer' => '/data/attributes/lastName']
                ]
            ],
            $response
        );
    }

    public function testCreateWithRequiredDataOnlyAndOrganization(): void
    {
        $customer = $this->getReference('customer');
        $ownerId = $customer->getOwner()->getId();
        $organizationId = $customer->getOrganization()->getId();
        $customerUserId = $this->getReference('customer_user')->getId();
        $countryId = $this->getReference('country_usa')->getIso2Code();
        $regionId = $this->getReference('region_usa_california')->getCombinedCode();

        $data = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        $response = $this->post(
            ['entity' => 'customeruseraddresses'],
            $data
        );

        $addressId = (int)$this->getResourceId($response);
        $responseContent = $data;
        $responseContent['data']['attributes']['label'] = null;
        $responseContent['data']['attributes']['primary'] = false;
        $responseContent['data']['attributes']['phone'] = null;
        $responseContent['data']['attributes']['street2'] = null;
        $responseContent['data']['attributes']['namePrefix'] = null;
        $responseContent['data']['attributes']['nameSuffix'] = null;
        $responseContent['data']['attributes']['firstName'] = null;
        $responseContent['data']['attributes']['middleName'] = null;
        $responseContent['data']['attributes']['lastName'] = null;
        $responseContent['data']['attributes']['validatedAt'] = null;
        $responseContent['data']['attributes']['types'] = [];
        $responseContent['data']['relationships']['customerUser']['data'] = [
            'type' => 'customerusers',
            'id'   => (string)$customerUserId
        ];
        $this->assertResponseContains($responseContent, $response);

        /** @var CustomerUserAddress $address */
        $address = $this->getEntityManager()
            ->find(CustomerUserAddress::class, $addressId);
        self::assertNotNull($address);
        self::assertNull($address->getLabel());
        self::assertFalse($address->isPrimary());
        self::assertNull($address->getPhone());
        self::assertEquals('Acme', $address->getOrganization());
        self::assertEquals('Street 1', $address->getStreet());
        self::assertNull($address->getStreet2());
        self::assertEquals('Los Angeles', $address->getCity());
        self::assertEquals('90001', $address->getPostalCode());
        self::assertNull($address->getNamePrefix());
        self::assertNull($address->getNameSuffix());
        self::assertNull($address->getFirstName());
        self::assertNull($address->getMiddleName());
        self::assertNull($address->getLastName());
        self::assertNull($address->getValidatedAt());
        self::assertCount(0, $address->getAddressTypes());
        self::assertEquals($organizationId, $address->getSystemOrganization()->getId());
        self::assertEquals($ownerId, $address->getOwner()->getId());
        self::assertEquals($customerUserId, $address->getFrontendOwner()->getId());
        self::assertEquals($countryId, $address->getCountry()->getIso2Code());
        self::assertEquals($regionId, $address->getRegion()->getCombinedCode());
    }

    public function testCreateWithRequiredDataOnlyAndFirstNameAndLastName(): void
    {
        $customer = $this->getReference('customer');
        $ownerId = $customer->getOwner()->getId();
        $organizationId = $customer->getOrganization()->getId();
        $customerUserId = $this->getReference('customer_user')->getId();
        $countryId = $this->getReference('country_usa')->getIso2Code();
        $regionId = $this->getReference('region_usa_california')->getCombinedCode();

        $data = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        unset($data['data']['attributes']['organization']);
        $data['data']['attributes']['firstName'] = 'John';
        $data['data']['attributes']['lastName'] = 'Doo';
        $response = $this->post(
            ['entity' => 'customeruseraddresses'],
            $data
        );

        $addressId = (int)$this->getResourceId($response);
        $responseContent = $data;
        $responseContent['data']['attributes']['label'] = null;
        $responseContent['data']['attributes']['primary'] = false;
        $responseContent['data']['attributes']['phone'] = null;
        $responseContent['data']['attributes']['organization'] = null;
        $responseContent['data']['attributes']['street2'] = null;
        $responseContent['data']['attributes']['namePrefix'] = null;
        $responseContent['data']['attributes']['nameSuffix'] = null;
        $responseContent['data']['attributes']['middleName'] = null;
        $responseContent['data']['attributes']['validatedAt'] = null;
        $responseContent['data']['attributes']['types'] = [];
        $responseContent['data']['relationships']['customerUser']['data'] = [
            'type' => 'customerusers',
            'id'   => (string)$customerUserId
        ];
        $this->assertResponseContains($responseContent, $response);

        /** @var CustomerUserAddress $address */
        $address = $this->getEntityManager()
            ->find(CustomerUserAddress::class, $addressId);
        self::assertNotNull($address);
        self::assertNull($address->getLabel());
        self::assertFalse($address->isPrimary());
        self::assertNull($address->getPhone());
        self::assertNull($address->getOrganization());
        self::assertEquals('Street 1', $address->getStreet());
        self::assertNull($address->getStreet2());
        self::assertEquals('Los Angeles', $address->getCity());
        self::assertEquals('90001', $address->getPostalCode());
        self::assertNull($address->getNamePrefix());
        self::assertNull($address->getNameSuffix());
        self::assertEquals('John', $address->getFirstName());
        self::assertNull($address->getMiddleName());
        self::assertNull($address->getValidatedAt());
        self::assertEquals('Doo', $address->getLastName());
        self::assertCount(0, $address->getAddressTypes());
        self::assertEquals($organizationId, $address->getSystemOrganization()->getId());
        self::assertEquals($ownerId, $address->getOwner()->getId());
        self::assertEquals($customerUserId, $address->getFrontendOwner()->getId());
        self::assertEquals($countryId, $address->getCountry()->getIso2Code());
        self::assertEquals($regionId, $address->getRegion()->getCombinedCode());
    }

    public function testTryToCreateWithNullCustomerUser(): void
    {
        $data = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        $data['data']['relationships']['customerUser']['data'] = null;
        $response = $this->post(
            ['entity' => 'customeruseraddresses'],
            $data,
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'not blank constraint',
                'detail' => 'This value should not be blank.',
                'source' => ['pointer' => '/data/relationships/customerUser/data']
            ],
            $response
        );
    }

    public function testUpdate(): void
    {
        $response = $this->patch(
            ['entity' => 'customeruseraddresses', 'id' => '<toString(@another_customer_user_address1->id)>'],
            'update_customer_user_address.yml'
        );

        $this->assertResponseContains('update_customer_user_address.yml', $response);

        /** @var CustomerUserAddress $address */
        $address = $this->getEntityManager()
            ->find(CustomerUserAddress::class, $this->getResourceId($response));

        self::assertEquals('City updated', $address->getCity());
        self::assertEquals('First name updated', $address->getFirstName());
        self::assertEquals('Address label updated', $address->getLabel());
        self::assertEquals('2024-10-11 00:00:00', $address->getValidatedAt()->format('Y-m-d H:i:s'));
        self::assertEquals(
            $this->getReference('another_customer_user')->getId(),
            $address->getFrontendOwner()->getId()
        );
        self::assertEquals(
            $this->getReference('country_usa')->getIso2Code(),
            $address->getCountry()->getIso2Code()
        );
    }

    public function testDelete(): void
    {
        $addressId = $this->getReference('another_customer_user_address1')->getId();

        $this->delete(
            ['entity' => 'customeruseraddresses', 'id' => $addressId]
        );

        $address = $this->getEntityManager()
            ->find(CustomerUserAddress::class, $addressId);
        self::assertTrue(null === $address);
    }

    public function testDeleteList(): void
    {
        $addressId = $this->getReference('another_customer_user_address1')->getId();

        $this->cdelete(
            ['entity' => 'customeruseraddresses'],
            ['filter' => ['id' => (string)$addressId]]
        );

        $address = $this->getEntityManager()
            ->find(CustomerUserAddress::class, $addressId);
        self::assertTrue(null === $address);
    }

    public function testTryToSetNullCountry(): void
    {
        $addressId = $this->getReference('another_customer_user_address1')->getId();
        $data = [
            'data' => [
                'type'          => 'customeruseraddresses',
                'id'            => (string)$addressId,
                'relationships' => [
                    'country' => [
                        'data' => null
                    ]
                ]
            ]
        ];
        $response = $this->patch(
            ['entity' => 'customeruseraddresses', 'id' => (string)$addressId],
            $data,
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'not blank constraint',
                'detail' => 'This value should not be blank.',
                'source' => ['pointer' => '/data/relationships/country/data']
            ],
            $response
        );
    }

    public function testTryToUpdateCustomerUser(): void
    {
        /** @var CustomerUserAddress $address */
        $address = $this->getReference('another_customer_user_address1');
        $addressId = $address->getId();
        $customerUserId = $address->getFrontendOwner()->getId();

        $response = $this->patch(
            ['entity' => 'customeruseraddresses', 'id' => (string)$addressId],
            [
                'data' => [
                    'type'          => 'customeruseraddresses',
                    'id'            => (string)$addressId,
                    'relationships' => [
                        'customerUser' => [
                            'data' => [
                                'type' => 'customerusers',
                                'id'   => '<toString(@customer_user1->id)>'
                            ]
                        ]
                    ]
                ]
            ]
        );

        $data['data']['relationships']['customerUser']['data']['id'] = (string)$customerUserId;
        $this->assertResponseContains($data, $response);
        self::assertSame(
            $customerUserId,
            $this->getEntityManager()->find(CustomerUserAddress::class, $addressId)->getFrontendOwner()->getId()
        );
    }

    public function testTryToUpdateCustomerUserViaRelationship(): void
    {
        $addressId = $this->getReference('another_customer_user_address1')->getId();
        $data = [
            'data' => [
                'type' => 'customerusers',
                'id'   => '<toString(@another_customer_user->id)>'
            ]
        ];

        $response = $this->patchRelationship(
            ['entity' => 'customeruseraddresses', 'id' => (string)$addressId, 'association' => 'customerUser'],
            $data,
            [],
            false
        );

        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }

    public function testTryToUpdateSystemOrganization(): void
    {
        $addressId = $this->getReference('another_customer_user_address1')->getId();

        $response = $this->patch(
            ['entity' => 'customeruseraddresses', 'id' => (string)$addressId],
            [
                'data' => [
                    'type'          => 'customeruseraddresses',
                    'id'            => (string)$addressId,
                    'relationships' => [
                        'systemOrganization' => [
                            'data' => [
                                'type' => 'organizations',
                                'id'   => '<toString(@organization->id)>'
                            ]
                        ]
                    ]
                ]
            ],
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'extra fields constraint',
                'detail' => 'This form should not contain extra fields: "systemOrganization".'
            ],
            $response
        );
    }

    public function testTryToUpdateSystemOrganizationViaRelationship(): void
    {
        $addressId = $this->getReference('another_customer_user_address1')->getId();

        $response = $this->patchRelationship(
            ['entity' => 'customeruseraddresses', 'id' => (string)$addressId, 'association' => 'systemOrganization'],
            [],
            [],
            false
        );
        $this->assertUnsupportedSubresourceResponse($response);
    }
}
