<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\AddressBundle\Tests\Functional\Api\RestJsonApi\AddressCountryAndRegionTestTrait;
use Oro\Bundle\AddressBundle\Tests\Functional\Api\RestJsonApi\PrimaryAddressTestTrait;
use Oro\Bundle\AddressBundle\Tests\Functional\Api\RestJsonApi\UnchangeableAddressOwnerTestTrait;
use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
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
class CustomerUserAddressTest extends RestJsonApiTestCase
{
    use AddressCountryAndRegionTestTrait;
    use PrimaryAddressTestTrait;
    use UnchangeableAddressOwnerTestTrait;
    use AddressTypeTestTrait;
    use RolePermissionExtension;

    protected const ENTITY_CLASS                   = CustomerUserAddress::class;
    protected const ENTITY_TYPE                    = 'customeruseraddresses';
    private const OWNER_ENTITY_TYPE                = 'customerusers';
    private const OWNER_RELATIONSHIP               = 'customerUser';
    private const CREATE_REQUEST_DATA              = 'create_customer_user_address.yml';
    private const CREATE_MIN_REQUEST_DATA          = 'create_customer_user_address_min.yml';
    private const OWNER_CREATE_MIN_REQUEST_DATA    = 'create_customer_user_min.yml';
    private const IS_REGION_REQUIRED               = true;
    private const COUNTRY_REGION_ADDRESS_REF       = 'other.user@test.com.address_1';
    private const PRIMARY_ADDRESS_REF              = 'other.user@test.com.address_1';
    private const DEFAULT_ADDRESS_REF              = 'other.user@test.com.address_1';
    private const BILLING_ADDRESS_REF              = 'grzegorz.brzeczyszczykiewicz@example.com.address_2';
    private const BILLING_AND_SHIPPING_ADDRESS_REF = 'grzegorz.brzeczyszczykiewicz@example.com.address_1';
    private const UNCHANGEABLE_ADDRESS_REF         = 'grzegorz.brzeczyszczykiewicz@example.com.address_1';
    private const OWNER_REF                        = 'grzegorz.brzeczyszczykiewicz@example.com';
    private const ANOTHER_OWNER_REF                = 'other.user@test.com';
    private const ANOTHER_OWNER_ADDRESS_2_REF      = 'other.user@test.com.address_2';

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures(['@OroCustomerBundle/Tests/Functional/Api/DataFixtures/customer_user_addresses.yml']);
        $role = $this->getEntityManager()
            ->getRepository(CustomerUserRole::class)
            ->findOneBy(['role' => 'ROLE_FRONTEND_ADMINISTRATOR']);
        $this->getReferenceRepository()->addReference('ROLE_FRONTEND_ADMINISTRATOR', $role);

        $this->updateRolePermissions(
            $role,
            CustomerUserAddress::class,
            [
                'CREATE' => AccessLevel::GLOBAL_LEVEL,
                'EDIT' => AccessLevel::GLOBAL_LEVEL,
                'VIEW' => AccessLevel::GLOBAL_LEVEL,
                'DELETE' => AccessLevel::GLOBAL_LEVEL,
            ]
        );
    }

    private function getOwner(CustomerUserAddress $address): ?CustomerUser
    {
        return $address->getFrontendOwner();
    }

    public function testGetList(): void
    {
        $response = $this->cget(
            ['entity' => self::ENTITY_TYPE]
        );

        $this->assertResponseContains('cget_customer_user_address.yml', $response);
    }

    public function testGetListFilterByAddressType(): void
    {
        $response = $this->cget(
            ['entity' => self::ENTITY_TYPE],
            ['filter' => ['addressType' => 'billing']]
        );

        $this->assertResponseContains(
            [
                'data' => [
                    [
                        'type' => self::ENTITY_TYPE,
                        'id'   => '<toString(@grzegorz.brzeczyszczykiewicz@example.com.address_1->id)>'
                    ],
                    [
                        'type' => self::ENTITY_TYPE,
                        'id'   => '<toString(@grzegorz.brzeczyszczykiewicz@example.com.address_2->id)>'
                    ],
                    [
                        'type' => self::ENTITY_TYPE,
                        'id'   => '<toString(@grzegorz.brzeczyszczykiewicz@example.com.address_3->id)>'
                    ],
                    [
                        'type' => self::ENTITY_TYPE,
                        'id'   => '<toString(@other.user@test.com.address_1->id)>'
                    ]
                ]
            ],
            $response
        );
    }

    public function testTryToGetListFilterByTypes(): void
    {
        $response = $this->cget(
            ['entity' => self::ENTITY_TYPE],
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
            ['entity' => self::ENTITY_TYPE, 'id' => '<toString(@other.user@test.com.address_1->id)>']
        );

        $this->assertResponseContains('get_customer_user_address.yml', $response);
    }

    public function testCreate(): void
    {
        $customer = $this->getReference('customer.1');
        $ownerId = $customer->getOwner()->getId();
        $organizationId = $customer->getOrganization()->getId();
        $customerUserId = $this->getReference('other.user@test.com')->getId();
        $countryId = $this->getReference('country.US')->getIso2Code();
        $regionId = $this->getReference('region.US-NY')->getCombinedCode();

        $response = $this->post(
            ['entity' => self::ENTITY_TYPE],
            'create_customer_user_address.yml'
        );

        $addressId = (int)$this->getResourceId($response);
        $responseContent = $this->updateResponseContent('create_customer_user_address.yml', $response);
        $this->assertResponseContains($responseContent, $response);

        /** @var CustomerUserAddress $address */
        $address = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, $addressId);
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

    public function testTryToCreateWhenCustomerOrganizationAndSystemOrganizationNotTheSame(): void
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

    public function testCreateWithRequiredDataOnlyAndOrganization(): void
    {
        $customer = $this->getReference('customer.1');
        $ownerId = $customer->getOwner()->getId();
        $organizationId = $customer->getOrganization()->getId();
        $customerUserId = $this->getReference('other.user@test.com')->getId();
        $countryId = $this->getReference('country.US')->getIso2Code();
        $regionId = $this->getReference('region.US-NY')->getCombinedCode();

        $data = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        $response = $this->post(
            ['entity' => self::ENTITY_TYPE],
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
        $responseContent['data']['attributes']['types'] = [];
        $responseContent['data']['attributes']['validatedAt'] = null;
        $responseContent['data']['relationships']['owner']['data'] = [
            'type' => 'users',
            'id'   => (string)$ownerId
        ];
        $responseContent['data']['relationships']['systemOrganization']['data'] = [
            'type' => 'organizations',
            'id'   => (string)$organizationId
        ];
        $this->assertResponseContains($responseContent, $response);

        /** @var CustomerUserAddress $address */
        $address = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, $addressId);
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
        $customer = $this->getReference('customer.1');
        $ownerId = $customer->getOwner()->getId();
        $organizationId = $customer->getOrganization()->getId();
        $customerUserId = $this->getReference('other.user@test.com')->getId();
        $countryId = $this->getReference('country.US')->getIso2Code();
        $regionId = $this->getReference('region.US-NY')->getCombinedCode();

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
        $responseContent['data']['relationships']['owner']['data'] = [
            'type' => 'users',
            'id'   => (string)$ownerId
        ];
        $responseContent['data']['relationships']['systemOrganization']['data'] = [
            'type' => 'organizations',
            'id'   => (string)$organizationId
        ];
        $this->assertResponseContains($responseContent, $response);

        /** @var CustomerUserAddress $address */
        $address = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, $addressId);
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
        self::assertNull($address->getValidatedAt());
        self::assertEquals('John', $address->getFirstName());
        self::assertNull($address->getMiddleName());
        self::assertEquals('Doo', $address->getLastName());
        self::assertCount(0, $address->getAddressTypes());
        self::assertEquals($organizationId, $address->getSystemOrganization()->getId());
        self::assertEquals($ownerId, $address->getOwner()->getId());
        self::assertEquals($customerUserId, $address->getFrontendOwner()->getId());
        self::assertEquals($countryId, $address->getCountry()->getIso2Code());
        self::assertEquals($regionId, $address->getRegion()->getCombinedCode());
    }

    public function testTryToCreateWithoutCustomerUser(): void
    {
        $data = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        unset($data['data']['relationships']['customerUser']);
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
                'source' => ['pointer' => '/data/relationships/customerUser/data']
            ],
            $response
        );
    }

    public function testTryToCreateWithNullCustomerUser(): void
    {
        $data = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        $data['data']['relationships']['customerUser']['data'] = null;
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
                'source' => ['pointer' => '/data/relationships/customerUser/data']
            ],
            $response
        );
    }

    public function testUpdate(): void
    {
        $response = $this->patch(
            ['entity' => self::ENTITY_TYPE, 'id' => '<toString(@other.user@test.com.address_1->id)>'],
            'update_customer_user_address.yml'
        );

        $this->assertResponseContains('update_customer_user_address.yml', $response);

        /** @var CustomerUserAddress $address */
        $address = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, $this->getResourceId($response));

        self::assertEquals('City updated', $address->getCity());
        self::assertEquals('First name updated', $address->getFirstName());
        self::assertEquals('Address label updated', $address->getLabel());
        self::assertEquals('2024-12-12 00:00:00', $address->getValidatedAt()->format('Y-m-d H:i:s'));
        self::assertEquals(
            $this->getReference('other.user@test.com')->getId(),
            $address->getFrontendOwner()->getId()
        );
        self::assertEquals(
            $this->getReference('country.US')->getIso2Code(),
            $address->getCountry()->getIso2Code()
        );
    }

    public function testDelete(): void
    {
        $addressId = $this->getReference('other.user@test.com.address_1')->getId();

        $this->delete(
            ['entity' => self::ENTITY_TYPE, 'id' => $addressId]
        );

        $address = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, $addressId);
        self::assertTrue(null === $address);
    }

    public function testDeleteList(): void
    {
        $addressId = $this->getReference('other.user@test.com.address_1')->getId();

        $this->cdelete(
            ['entity' => self::ENTITY_TYPE],
            ['filter' => ['id' => (string)$addressId]]
        );

        $address = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, $addressId);
        self::assertTrue(null === $address);
    }

    public function testTryToSetNullCountry(): void
    {
        $addressId = $this->getReference('other.user@test.com.address_1')->getId();
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

    public function testTryToUpdateCustomerUser(): void
    {
        /** @var CustomerUserAddress $address */
        $address = $this->getReference('other.user@test.com.address_1');
        $addressId = $address->getId();
        $customerUserId = $address->getFrontendOwner()->getId();

        $response = $this->patch(
            ['entity' => self::ENTITY_TYPE, 'id' => $addressId],
            [
                'data' => [
                    'type'          => self::ENTITY_TYPE,
                    'id'            => (string)$addressId,
                    'relationships' => [
                        'customerUser' => [
                            'data' => [
                                'type' => 'customerusers',
                                'id'   => '<toString(@grzegorz.brzeczyszczykiewicz@example.com->id)>'
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
            $this->getEntityManager()->find(self::ENTITY_CLASS, $addressId)->getFrontendOwner()->getId()
        );
    }

    public function testTryToUpdateCustomerUserViaRelationship(): void
    {
        $addressId = $this->getReference('other.user@test.com.address_1')->getId();
        $data = [
            'data' => [
                'type' => 'customerusers',
                'id'   => '<toString(@other.user@test.com->id)>'
            ]
        ];

        $response = $this->patchRelationship(
            ['entity' => self::ENTITY_TYPE, 'id' => (string)$addressId, 'association' => 'customerUser'],
            $data,
            [],
            false
        );

        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }

    public function testTryToUpdateSystemOrganization(): void
    {
        /** @var CustomerUserAddress $address */
        $address = $this->getReference('other.user@test.com.address_1');
        $addressId = $address->getId();
        $organizationId = $address->getSystemOrganization()->getId();

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

        self::assertSame(
            $organizationId,
            $this->getEntityManager()->find(self::ENTITY_CLASS, $addressId)->getSystemOrganization()->getId()
        );
    }

    public function testTryToUpdateSystemOrganizationViaRelationship(): void
    {
        $addressId = $this->getReference('other.user@test.com.address_1')->getId();
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
