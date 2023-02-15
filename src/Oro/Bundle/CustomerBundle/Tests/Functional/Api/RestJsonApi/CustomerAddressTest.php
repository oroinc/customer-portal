<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\AddressBundle\Tests\Functional\Api\RestJsonApi\AddressCountryAndRegionTestTrait;
use Oro\Bundle\AddressBundle\Tests\Functional\Api\RestJsonApi\PrimaryAddressTestTrait;
use Oro\Bundle\AddressBundle\Tests\Functional\Api\RestJsonApi\UnchangeableAddressOwnerTestTrait;
use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Test\Functional\RolePermissionExtension;

/**
 * @group CommunityEdition
 *
 * @dbIsolationPerTest
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class CustomerAddressTest extends RestJsonApiTestCase
{
    use AddressCountryAndRegionTestTrait;
    use PrimaryAddressTestTrait;
    use UnchangeableAddressOwnerTestTrait;
    use AddressTypeTestTrait;
    use RolePermissionExtension;

    protected const ENTITY_CLASS                   = CustomerAddress::class;
    protected const ENTITY_TYPE                    = 'customeraddresses';
    private const OWNER_ENTITY_TYPE                = 'customers';
    private const OWNER_RELATIONSHIP               = 'customer';
    private const CREATE_REQUEST_DATA              = 'create_customer_address.yml';
    private const CREATE_MIN_REQUEST_DATA          = 'create_customer_address_min.yml';
    private const OWNER_CREATE_MIN_REQUEST_DATA    = 'create_customer_min.yml';
    private const IS_REGION_REQUIRED               = true;
    private const COUNTRY_REGION_ADDRESS_REF       = 'customer.level_1.1.address_1';
    private const PRIMARY_ADDRESS_REF              = 'customer.level_1.1.address_1';
    private const DEFAULT_ADDRESS_REF              = 'customer.level_1.1.address_1';
    private const BILLING_ADDRESS_REF              = 'customer.level_1.address_2';
    private const BILLING_AND_SHIPPING_ADDRESS_REF = 'customer.level_1.address_1';
    private const UNCHANGEABLE_ADDRESS_REF         = 'customer.level_1.address_1';
    private const OWNER_REF                        = 'customer.level_1';
    private const ANOTHER_OWNER_REF                = 'customer.level_1.1';
    private const ANOTHER_OWNER_ADDRESS_2_REF      = 'customer.level_1.1.address_2';
    private const CREATE_WITH_SYSTEM_ORGANIZATION_DATA = 'create_customer_address_with_system_organization.yml';

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures(['@OroCustomerBundle/Tests/Functional/Api/DataFixtures/customer_addresses.yml']);

        $role = $this->getEntityManager()
            ->getRepository(CustomerUserRole::class)
            ->findOneBy(['role' => 'ROLE_FRONTEND_ADMINISTRATOR']);
        $this->getReferenceRepository()->addReference('ROLE_FRONTEND_ADMINISTRATOR', $role);

        $this->updateRolePermissions(
            $role,
            CustomerAddress::class,
            [
                'CREATE' => AccessLevel::GLOBAL_LEVEL,
                'EDIT' => AccessLevel::GLOBAL_LEVEL,
                'VIEW' => AccessLevel::GLOBAL_LEVEL,
                'DELETE' => AccessLevel::GLOBAL_LEVEL,
            ]
        );
    }

    /**
     * @param CustomerAddress $address
     *
     * @return Customer
     */
    private function getOwner(CustomerAddress $address)
    {
        return $address->getFrontendOwner();
    }

    public function testGetList()
    {
        $response = $this->cget(
            ['entity' => self::ENTITY_TYPE]
        );

        $this->assertResponseContains('cget_customer_address.yml', $response);
    }

    public function testGetListFilterByAddressType()
    {
        $response = $this->cget(
            ['entity' => self::ENTITY_TYPE],
            ['filter' => ['addressType' => 'shipping']]
        );

        $this->assertResponseContains('cget_customer_address_filter_type.yml', $response);
    }

    public function testTryToGetListFilterByTypes()
    {
        $response = $this->cget(
            ['entity' => self::ENTITY_TYPE],
            ['filter' => ['types' => 'shipping']],
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

    public function testGet()
    {
        $response = $this->get(
            ['entity' => self::ENTITY_TYPE, 'id' => '<toString(@customer.level_1.address_1->id)>']
        );

        $this->assertResponseContains('get_customer_address.yml', $response);
    }

    public function testCreate()
    {
        $userId = $this->getReference('user')->getId();
        $organizationId = $this->getReference('user')->getOrganization()->getId();
        $customerId = $this->getReference('customer.level_1')->getId();
        $countryId = $this->getEntityManager()->find(Country::class, 'US')->getIso2Code();
        $regionId = $this->getEntityManager()->find(Region::class, 'US-NY')->getCombinedCode();

        $response = $this->post(
            ['entity' => self::ENTITY_TYPE],
            'create_customer_address.yml'
        );

        $addressId = (int)$this->getResourceId($response);
        $responseContent = $this->updateResponseContent('create_customer_address.yml', $response);
        $this->assertResponseContains($responseContent, $response);

        /** @var CustomerAddress $address */
        $address = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, $addressId);
        self::assertNotNull($address);
        self::assertEquals('New Address', $address->getLabel());
        self::assertTrue($address->isPrimary());
        self::assertEquals('123-456', $address->getPhone());
        self::assertEquals('Street 1', $address->getStreet());
        self::assertEquals('Street 2', $address->getStreet2());
        self::assertEquals('Los Angeles', $address->getCity());
        self::assertEquals('90001', $address->getPostalCode());
        self::assertEquals('Acme', $address->getOrganization());
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
        self::assertEquals($userId, $address->getOwner()->getId());
        self::assertEquals($customerId, $address->getFrontendOwner()->getId());
        self::assertEquals($countryId, $address->getCountry()->getIso2Code());
        self::assertEquals($regionId, $address->getRegion()->getCombinedCode());
    }

    public function testTryToCreateWithRequiredDataOnlyAndWithoutOrganizationAndFirstNameAndLastName()
    {
        $data = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        unset($data['data']['attributes']['organization']);
        $response = $this->post(
            ['entity' => self::ENTITY_TYPE],
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

    public function testTryToCreateWhenCustomerOrganizationAndSystemOrganizationNotTheSame()
    {
        $data = $this->getRequestData(self::CREATE_REQUEST_DATA);
        $data['data']['relationships']['systemOrganization'] = [
            'data' => [
                'type' => 'organizations',
                'id' => '<toString(@another_organization->id)>',
            ]
        ];

        $response = $this->post(
            ['entity' => self::ENTITY_TYPE],
            $data,
            [],
            false
        );

        $errors = self::jsonToArray($response->getContent())['errors'] ?? [];

        if (\count($errors) === 3) {
            $this->assertResponseValidationErrors(
                [
                    [
                        'title' => 'valid organization constraint',
                        'detail' => 'Customer and its address belongs to different organizations.',
                    ],
                    [
                        'title' => 'organization constraint',
                        'detail' => 'You have no access to set this value as systemOrganization.',
                        'source' => ['pointer' => '/data/relationships/systemOrganization/data']
                    ],
                    [
                        'title' => 'access granted constraint',
                        'detail' => 'The "VIEW" permission is denied for the related resource.',
                        'source' => ['pointer' => '/data/relationships/systemOrganization/data']
                    ]
                ],
                $response
            );
        } else {
            $this->assertResponseValidationError(
                [
                    'title' => 'valid organization constraint',
                    'detail' => 'Customer and its address belongs to different organizations.',
                ],
                $response
            );
        }
    }

    public function testCreateWithRequiredDataOnlyAndOrganization()
    {
        $ownerId = $this->getReference('user')->getId();
        $organizationId = $this->getReference('user')->getOrganization()->getId();
        $customerId = $this->getReference('customer.level_1')->getId();
        $countryId = $this->getEntityManager()->find(Country::class, 'US')->getIso2Code();
        $regionId = $this->getEntityManager()->find(Region::class, 'US-NY')->getCombinedCode();

        $data = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        $response = $this->post(
            ['entity' => self::ENTITY_TYPE],
            $data
        );

        $addressId = (int)$this->getResourceId($response);
        $responseContent = $data;
        $responseContent['data']['attributes']['phone'] = null;
        $responseContent['data']['attributes']['primary'] = false;
        $responseContent['data']['attributes']['label'] = null;
        $responseContent['data']['attributes']['street2'] = null;
        $responseContent['data']['attributes']['namePrefix'] = null;
        $responseContent['data']['attributes']['nameSuffix'] = null;
        $responseContent['data']['attributes']['firstName'] = null;
        $responseContent['data']['attributes']['middleName'] = null;
        $responseContent['data']['attributes']['lastName'] = null;
        $responseContent['data']['attributes']['types'] = [];
        $responseContent['data']['relationships'][self::OWNER_RELATIONSHIP]['data'] = [
            'type' => self::OWNER_ENTITY_TYPE,
            'id'   => (string)$customerId
        ];
        $responseContent['data']['relationships']['owner']['data'] = [
            'type' => 'users',
            'id'   => (string)$ownerId
        ];
        $responseContent['data']['relationships']['systemOrganization']['data'] = [
            'type' => 'organizations',
            'id'   => (string)$organizationId
        ];
        $this->assertResponseContains($responseContent, $response);

        /** @var CustomerAddress $address */
        $address = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, $addressId);
        self::assertNotNull($address);
        self::assertNull($address->getLabel());
        self::assertFalse($address->isPrimary());
        self::assertNull($address->getPhone());
        self::assertEquals('Street 1', $address->getStreet());
        self::assertNull($address->getStreet2());
        self::assertEquals('Los Angeles', $address->getCity());
        self::assertEquals('90001', $address->getPostalCode());
        self::assertEquals('Acme', $address->getOrganization());
        self::assertNull($address->getNamePrefix());
        self::assertNull($address->getNameSuffix());
        self::assertNull($address->getFirstName());
        self::assertNull($address->getMiddleName());
        self::assertNull($address->getLastName());
        self::assertCount(0, $address->getAddressTypes());
        self::assertEquals($organizationId, $address->getSystemOrganization()->getId());
        self::assertEquals($ownerId, $address->getOwner()->getId());
        self::assertEquals($customerId, $address->getFrontendOwner()->getId());
        self::assertEquals($countryId, $address->getCountry()->getIso2Code());
        self::assertEquals($regionId, $address->getRegion()->getCombinedCode());
    }

    public function testCreateWithRequiredDataOnlyAndFirstNameAndLastName()
    {
        $ownerId = $this->getReference('user')->getId();
        $organizationId = $this->getReference('user')->getOrganization()->getId();
        $customerId = $this->getReference('customer.level_1')->getId();
        $countryId = $this->getEntityManager()->find(Country::class, 'US')->getIso2Code();
        $regionId = $this->getEntityManager()->find(Region::class, 'US-NY')->getCombinedCode();

        $data = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        unset($data['data']['attributes']['organization']);
        $data['data']['attributes']['firstName'] = 'John';
        $data['data']['attributes']['lastName'] = 'Doo';
        $response = $this->post(
            ['entity' => self::ENTITY_TYPE],
            $data
        );

        $addressId = (int)$this->getResourceId($response);
        $responseContent = $data;
        $responseContent['data']['attributes']['phone'] = null;
        $responseContent['data']['attributes']['primary'] = false;
        $responseContent['data']['attributes']['label'] = null;
        $responseContent['data']['attributes']['street2'] = null;
        $responseContent['data']['attributes']['organization'] = null;
        $responseContent['data']['attributes']['namePrefix'] = null;
        $responseContent['data']['attributes']['nameSuffix'] = null;
        $responseContent['data']['attributes']['middleName'] = null;
        $responseContent['data']['attributes']['types'] = [];
        $responseContent['data']['relationships'][self::OWNER_RELATIONSHIP]['data'] = [
            'type' => self::OWNER_ENTITY_TYPE,
            'id'   => (string)$customerId
        ];
        $responseContent['data']['relationships']['owner']['data'] = [
            'type' => 'users',
            'id'   => (string)$ownerId
        ];
        $responseContent['data']['relationships']['systemOrganization']['data'] = [
            'type' => 'organizations',
            'id'   => (string)$organizationId
        ];
        $this->assertResponseContains($responseContent, $response);

        /** @var CustomerAddress $address */
        $address = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, $addressId);
        self::assertNotNull($address);
        self::assertNull($address->getLabel());
        self::assertFalse($address->isPrimary());
        self::assertNull($address->getPhone());
        self::assertEquals('Street 1', $address->getStreet());
        self::assertNull($address->getStreet2());
        self::assertEquals('Los Angeles', $address->getCity());
        self::assertEquals('90001', $address->getPostalCode());
        self::assertNull($address->getOrganization());
        self::assertNull($address->getNamePrefix());
        self::assertNull($address->getNameSuffix());
        self::assertEquals('John', $address->getFirstName());
        self::assertNull($address->getMiddleName());
        self::assertEquals('Doo', $address->getLastName());
        self::assertCount(0, $address->getAddressTypes());
        self::assertEquals($organizationId, $address->getSystemOrganization()->getId());
        self::assertEquals($ownerId, $address->getOwner()->getId());
        self::assertEquals($customerId, $address->getFrontendOwner()->getId());
        self::assertEquals($countryId, $address->getCountry()->getIso2Code());
        self::assertEquals($regionId, $address->getRegion()->getCombinedCode());
    }

    public function testTryToCreateWithoutCustomer()
    {
        $data = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        unset($data['data']['relationships'][self::OWNER_RELATIONSHIP]);
        $response = $this->post(
            ['entity' => self::ENTITY_TYPE],
            $data,
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'not blank constraint',
                'detail' => 'This value should not be blank.',
                'source' => ['pointer' => '/data/relationships/customer/data']
            ],
            $response
        );
    }

    public function testTryToCreateWithNullCustomer()
    {
        $data = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        $data['data']['relationships'][self::OWNER_RELATIONSHIP]['data'] = null;
        $response = $this->post(
            ['entity' => self::ENTITY_TYPE],
            $data,
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'not blank constraint',
                'detail' => 'This value should not be blank.',
                'source' => ['pointer' => '/data/relationships/customer/data']
            ],
            $response
        );
    }

    public function testUpdate()
    {
        $addressId = $this->getReference('customer.level_1.address_1')->getId();
        $data = [
            'data' => [
                'type'       => self::ENTITY_TYPE,
                'id'         => (string)$addressId,
                'attributes' => [
                    'label' => 'Updated Address'
                ]
            ]
        ];

        $response = $this->patch(
            ['entity' => self::ENTITY_TYPE, 'id' => (string)$addressId],
            $data
        );

        $this->assertResponseContains($data, $response);

        /** @var CustomerAddress $address */
        $address = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, $addressId);
        self::assertNotNull($address);
        self::assertEquals('Updated Address', $address->getLabel());
    }

    public function testDelete()
    {
        $addressId = $this->getReference('customer.level_1.address_1')->getId();

        $this->delete(
            ['entity' => self::ENTITY_TYPE, 'id' => (string)$addressId]
        );

        $address = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, $addressId);
        self::assertTrue(null === $address);
    }

    public function testDeleteList()
    {
        $addressId = $this->getReference('customer.level_1.address_1')->getId();

        $this->cdelete(
            ['entity' => self::ENTITY_TYPE],
            ['filter' => ['id' => (string)$addressId]]
        );

        $address = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, $addressId);
        self::assertTrue(null === $address);
    }

    public function testTryToSetNullCountry()
    {
        $addressId = $this->getReference('customer.level_1.address_1')->getId();
        $data = [
            'data' => [
                'type'          => self::ENTITY_TYPE,
                'id'            => (string)$addressId,
                'relationships' => [
                    'country' => [
                        'data' => null
                    ]
                ]
            ]
        ];
        $response = $this->patch(
            ['entity' => self::ENTITY_TYPE, 'id' => $addressId],
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

    public function testTryToUpdateCustomer()
    {
        /** @var CustomerAddress $address */
        $address = $this->getReference('customer.level_1.address_1');
        $addressId = $address->getId();
        $customerId = $address->getFrontendOwner()->getId();
        $data = [
            'data' => [
                'type'          => self::ENTITY_TYPE,
                'id'            => (string)$addressId,
                'relationships' => [
                    self::OWNER_RELATIONSHIP => [
                        'data' => [
                            'type' => self::OWNER_ENTITY_TYPE,
                            'id'   => '<toString(@customer.level_1.1->id)>'
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->patch(
            ['entity' => self::ENTITY_TYPE, 'id' => (string)$addressId],
            $data
        );

        $data['data']['relationships'][self::OWNER_RELATIONSHIP]['data']['id'] = (string)$customerId;
        $this->assertResponseContains($data, $response);
        self::assertSame(
            $customerId,
            $this->getEntityManager()->find(self::ENTITY_CLASS, $addressId)->getFrontendOwner()->getId()
        );
    }

    public function testTryToUpdateCustomerViaRelationship()
    {
        $addressId = $this->getReference('customer.level_1.address_1')->getId();
        $data = [
            'data' => [
                'type' => self::OWNER_ENTITY_TYPE,
                'id'   => '<toString(@customer.level_1->id)>'
            ]
        ];

        $response = $this->patchRelationship(
            ['entity' => self::ENTITY_TYPE, 'id' => (string)$addressId, 'association' => self::OWNER_RELATIONSHIP],
            $data,
            [],
            false
        );

        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }

    public function testTryToUpdateSystemOrganization()
    {
        /** @var CustomerAddress $address */
        $address = $this->getReference('customer.level_1.address_1');
        $addressId = $address->getId();

        $response = $this->patch(
            ['entity' => self::ENTITY_TYPE, 'id' => $addressId],
            [
                'data' => [
                    'type'          => self::ENTITY_TYPE,
                    'id'            => (string)$addressId,
                    'relationships' => [
                        'systemOrganization' => [
                            'data' => [
                                'type' => 'organizations',
                                'id'   => '<toString(@another_organization->id)>'
                            ]
                        ]
                    ]
                ]
            ],
            [],
            false
        );

        $errors = self::jsonToArray($response->getContent())['errors'] ?? [];

        if (\count($errors) === 3) {
            $this->assertResponseValidationErrors(
                [
                    [
                        'title' => 'valid organization constraint',
                        'detail' => 'Customer and its address belongs to different organizations.',
                    ],
                    [
                        'title' => 'organization constraint',
                        'detail' => 'You have no access to set this value as systemOrganization.',
                        'source' => ['pointer' => '/data/relationships/systemOrganization/data']
                    ],
                    [
                        'title' => 'access granted constraint',
                        'detail' => 'The "VIEW" permission is denied for the related resource.',
                        'source' => ['pointer' => '/data/relationships/systemOrganization/data']
                    ]
                ],
                $response
            );
        } else {
            $this->assertResponseValidationError(
                [
                    'title' => 'valid organization constraint',
                    'detail' => 'Customer and its address belongs to different organizations.',
                ],
                $response
            );
        }
    }

    public function testTryToUpdateSystemOrganizationViaRelationship()
    {
        $addressId = $this->getReference('customer.level_1.address_1')->getId();
        $data = [
            'data' => [
                'type' => 'organizations',
                'id'   => '<toString(@organization->id)>'
            ]
        ];

        $response = $this->patchRelationship(
            ['entity' => self::ENTITY_TYPE, 'id' => (string)$addressId, 'association' => 'systemOrganization'],
            $data,
            [],
            false
        );

        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }
}
