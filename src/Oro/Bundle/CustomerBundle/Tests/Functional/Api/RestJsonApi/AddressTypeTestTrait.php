<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\RestJsonApi;

use Doctrine\Common\Collections\Collection;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\ApiBundle\Request\ApiAction;
use Oro\Bundle\CustomerBundle\Entity\AbstractAddressToAddressType;
use Oro\Bundle\CustomerBundle\Entity\AbstractDefaultTypedAddress;

/**
 * Tests for customer and customer user address types.
 * This trait requires the following constants:
 * * ENTITY_CLASS
 * * ENTITY_TYPE
 * * OWNER_ENTITY_TYPE
 * * OWNER_RELATIONSHIP
 * * CREATE_MIN_REQUEST_DATA
 * * BILLING_ADDRESS_REF
 * * BILLING_AND_SHIPPING_ADDRESS_REF
 * * DEFAULT_ADDRESS_REF
 * and the following methods:
 * * getOwner($address)
 */
trait AddressTypeTestTrait
{
    /**
     * @param AbstractDefaultTypedAddress $address
     * @param string                      $type
     *
     * @return AbstractAddressToAddressType
     */
    private function getAddressType(AbstractDefaultTypedAddress $address, $type)
    {
        $result = null;
        foreach ($address->getAddressTypes() as $addressType) {
            if ($type === $addressType->getType()->getName()) {
                $result = $addressType;
            }
        }
        self::assertTrue(
            null !== $result,
            sprintf('Address "%s" does not have type "%s"', $address->getId(), $type)
        );

        return $result;
    }

    /**
     * @param array                                     $expected
     * @param Collection|AbstractAddressToAddressType[] $actual
     */
    private static function assertAddressTypes(array $expected, Collection $actual)
    {
        self::assertCount(count($expected), $actual);
        $actualData = [];
        foreach ($actual as $type) {
            $actualData[] = [
                'default'     => $type->isDefault(),
                'addressType' => $type->getType()->getName()
            ];
        }
        self::assertArrayContains($expected, $actualData);
    }

    /**
     * @param array                                     $expectedIds
     * @param Collection|AbstractAddressToAddressType[] $actual
     */
    private static function assertAddressTypeIds(array $expectedIds, Collection $actual)
    {
        $actualIds = [];
        foreach ($actual as $type) {
            $actualIds[] = $type->getId();
        }
        self::assertArrayContains($expectedIds, $actualIds);
    }

    public function testTryToCreateTypesWhenOneOfTypeIsInvalid()
    {
        $data = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        $data['data']['attributes']['types'] = [
            ['addressType' => 'shipping', 'default' => true],
            'test'
        ];

        $response = $this->post(
            ['entity' => self::ENTITY_TYPE],
            $data,
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'form constraint',
                'detail' => 'This value is not valid.',
                'source' => ['pointer' => '/data/attributes/types/1']
            ],
            $response
        );
    }

    public function testTryToCreateWhenOneOfTypeIsNull()
    {
        $data = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        $data['data']['attributes']['types'] = [
            ['addressType' => 'shipping', 'default' => true],
            null
        ];

        $response = $this->post(
            ['entity' => self::ENTITY_TYPE],
            $data,
            [],
            false
        );

        $this->assertResponseValidationErrors(
            [
                [
                    'title'  => 'form constraint',
                    'detail' => 'This value is mandatory.',
                    'source' => ['pointer' => '/data/attributes/types/1/default']
                ],
                [
                    'title'  => 'form constraint',
                    'detail' => 'This value is mandatory.',
                    'source' => ['pointer' => '/data/attributes/types/1/addressType']
                ],
                [
                    'title'  => 'form constraint',
                    'detail' => 'This value is not valid.',
                    'source' => ['pointer' => '/data/attributes/types/1/addressType']
                ]
            ],
            $response
        );
    }

    public function testTryToCreateWhenOneOfTypeIsEmptyString()
    {
        $data = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        $data['data']['attributes']['types'] = [
            ['addressType' => 'shipping', 'default' => true],
            ''
        ];

        $response = $this->post(
            ['entity' => self::ENTITY_TYPE],
            $data,
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'form constraint',
                'detail' => 'This value is not valid.',
                'source' => ['pointer' => '/data/attributes/types/1']
            ],
            $response
        );
    }

    public function testTryToCreateWhenOneOfTypeIsEmptyArray()
    {
        $data = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        $data['data']['attributes']['types'] = [
            ['addressType' => 'shipping', 'default' => true],
            []
        ];

        $response = $this->post(
            ['entity' => self::ENTITY_TYPE],
            $data,
            [],
            false
        );

        $this->assertResponseValidationErrors(
            [
                [
                    'title'  => 'form constraint',
                    'detail' => 'This value is mandatory.',
                    'source' => ['pointer' => '/data/attributes/types/1/default']
                ],
                [
                    'title'  => 'form constraint',
                    'detail' => 'This value is mandatory.',
                    'source' => ['pointer' => '/data/attributes/types/1/addressType']
                ],
                [
                    'title'  => 'form constraint',
                    'detail' => 'This value is not valid.',
                    'source' => ['pointer' => '/data/attributes/types/1/addressType']
                ]
            ],
            $response
        );
    }

    public function testTryToCreateWhenAddressTypePropertyForOneOfTypeIsNull()
    {
        $data = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        $data['data']['attributes']['types'] = [
            ['addressType' => null, 'default' => false]
        ];

        $response = $this->post(
            ['entity' => self::ENTITY_TYPE],
            $data,
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'form constraint',
                'detail' => 'This value is not valid.',
                'source' => ['pointer' => '/data/attributes/types/0/addressType']
            ],
            $response
        );
    }

    public function testTryToCreateWhenAddressTypePropertyForOneOfTypeIsEmptyString()
    {
        $data = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        $data['data']['attributes']['types'] = [
            ['addressType' => '', 'default' => false]
        ];

        $response = $this->post(
            ['entity' => self::ENTITY_TYPE],
            $data,
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'form constraint',
                'detail' => 'This value is not valid.',
                'source' => ['pointer' => '/data/attributes/types/0/addressType']
            ],
            $response
        );
    }

    public function testTryToCreateWhenDefaultOptionForOneOfTypeIsNotBool()
    {
        $data = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        $data['data']['attributes']['types'] = [
            ['addressType' => 'shipping', 'default' => 'test']
        ];

        $response = $this->post(
            ['entity' => self::ENTITY_TYPE],
            $data,
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'form constraint',
                'detail' => 'This value is not valid.',
                'source' => ['pointer' => '/data/attributes/types/0/default']
            ],
            $response
        );
    }

    public function testTryToCreateWhenDefaultOptionForOneOfTypeIsMissing()
    {
        $data = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        $data['data']['attributes']['types'] = [
            ['addressType' => 'shipping']
        ];

        $response = $this->post(
            ['entity' => self::ENTITY_TYPE],
            $data,
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'form constraint',
                'detail' => 'This value is mandatory.',
                'source' => ['pointer' => '/data/attributes/types/0/default']
            ],
            $response
        );
    }

    public function testTryToCreateWhenAddressTypePropertyForOneOfTypeIsNotExistingAddressType()
    {
        $data = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        $data['data']['attributes']['types'] = [
            ['addressType' => 'test', 'default' => true]
        ];

        $response = $this->post(
            ['entity' => self::ENTITY_TYPE],
            $data,
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'form constraint',
                'detail' => 'This value is not valid.',
                'source' => ['pointer' => '/data/attributes/types/0/addressType']
            ],
            $response
        );
    }

    public function testTryToCreateWhenAddressTypePropertyForOneOfTypeIsMissing()
    {
        $data = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        $data['data']['attributes']['types'] = [
            ['default' => true]
        ];

        $response = $this->post(
            ['entity' => self::ENTITY_TYPE],
            $data,
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'form constraint',
                'detail' => 'This value is mandatory.',
                'source' => ['pointer' => '/data/attributes/types/0/addressType']
            ],
            $response
        );
    }

    public function testUpdateTypesWhenNewTypeIsAdded()
    {
        /** @var AbstractDefaultTypedAddress $address */
        $address = $this->getReference(self::BILLING_ADDRESS_REF);
        $addressId = $address->getId();
        $addressTypeId = $this->getAddressType($address, 'billing')->getId();
        $data = [
            'data' => [
                'type'       => self::ENTITY_TYPE,
                'id'         => (string)$addressId,
                'attributes' => [
                    'types' => [
                        ['addressType' => 'billing', 'default' => true],
                        ['addressType' => 'shipping', 'default' => true]
                    ]
                ]
            ]
        ];

        $response = $this->patch(
            ['entity' => self::ENTITY_TYPE, 'id' => (string)$addressId],
            $data
        );

        $this->assertResponseContains($data, $response);

        /** @var AbstractDefaultTypedAddress $address */
        $address = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, $addressId);
        self::assertNotNull($address);
        self::assertAddressTypes($data['data']['attributes']['types'], $address->getAddressTypes());
        self::assertAddressTypeIds([$addressTypeId], $address->getAddressTypes());
    }

    public function testUpdateTypesWhenExistingTypeIsRemoved()
    {
        /** @var AbstractDefaultTypedAddress $address */
        $address = $this->getReference(self::BILLING_AND_SHIPPING_ADDRESS_REF);
        $addressId = $address->getId();
        $addressTypeId = $this->getAddressType($address, 'shipping')->getId();
        $data = [
            'data' => [
                'type'       => self::ENTITY_TYPE,
                'id'         => (string)$addressId,
                'attributes' => [
                    'types' => [
                        ['addressType' => 'shipping', 'default' => false]
                    ]
                ]
            ]
        ];

        $response = $this->patch(
            ['entity' => self::ENTITY_TYPE, 'id' => (string)$addressId],
            $data
        );

        $this->assertResponseContains($data, $response);

        /** @var AbstractDefaultTypedAddress $address */
        $address = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, $addressId);
        self::assertNotNull($address);
        self::assertAddressTypes($data['data']['attributes']['types'], $address->getAddressTypes());
        self::assertAddressTypeIds([$addressTypeId], $address->getAddressTypes());
    }

    public function testUpdateTypesWhenNoChangesButOrderOfRequestDataDifferentThanOrderOfDatabaseData()
    {
        /** @var AbstractDefaultTypedAddress $address */
        $address = $this->getReference(self::BILLING_AND_SHIPPING_ADDRESS_REF);
        $addressId = $address->getId();
        $addressType1Id = $this->getAddressType($address, 'billing')->getId();
        $addressType2Id = $this->getAddressType($address, 'shipping')->getId();
        $data = [
            'data' => [
                'type'       => self::ENTITY_TYPE,
                'id'         => (string)$addressId,
                'attributes' => [
                    'types' => [
                        ['addressType' => 'shipping', 'default' => false],
                        ['addressType' => 'billing', 'default' => true]
                    ]
                ]
            ]
        ];

        $response = $this->patch(
            ['entity' => self::ENTITY_TYPE, 'id' => (string)$addressId],
            $data
        );

        $this->assertResponseContains($data, $response);

        /** @var AbstractDefaultTypedAddress $address */
        $address = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, $addressId);
        self::assertNotNull($address);
        self::assertAddressTypes($data['data']['attributes']['types'], $address->getAddressTypes());
        self::assertAddressTypeIds([$addressType1Id, $addressType2Id], $address->getAddressTypes());
    }

    public function testUpdateTypesWhenDefaultOptionForExistingTypeIsChanged()
    {
        /** @var AbstractDefaultTypedAddress $address */
        $address = $this->getReference(self::BILLING_ADDRESS_REF);
        $addressId = $address->getId();
        $addressTypeId = $this->getAddressType($address, 'billing')->getId();
        $data = [
            'data' => [
                'type'       => self::ENTITY_TYPE,
                'id'         => (string)$addressId,
                'attributes' => [
                    'types' => [
                        ['addressType' => 'billing', 'default' => false]
                    ]
                ]
            ]
        ];

        $response = $this->patch(
            ['entity' => self::ENTITY_TYPE, 'id' => (string)$addressId],
            $data
        );

        $this->assertResponseContains($data, $response);

        /** @var AbstractDefaultTypedAddress $address */
        $address = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, $addressId);
        self::assertNotNull($address);
        self::assertAddressTypes($data['data']['attributes']['types'], $address->getAddressTypes());
        self::assertAddressTypeIds([$addressTypeId], $address->getAddressTypes());
    }

    public function testTryToUpdateTypesWhenOneOfTypeIsInvalid()
    {
        $addressId = $this->getReference(self::BILLING_ADDRESS_REF)->getId();
        $data = [
            'data' => [
                'type'       => self::ENTITY_TYPE,
                'id'         => (string)$addressId,
                'attributes' => [
                    'types' => [
                        ['addressType' => 'shipping', 'default' => true],
                        'test'
                    ]
                ]
            ]
        ];

        $response = $this->patch(
            ['entity' => self::ENTITY_TYPE, 'id' => (string)$addressId],
            $data,
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'form constraint',
                'detail' => 'This value is not valid.',
                'source' => ['pointer' => '/data/attributes/types/1']
            ],
            $response
        );
    }

    public function testTryToUpdateTypesWhenOneOfTypeIsNull()
    {
        $addressId = $this->getReference(self::BILLING_ADDRESS_REF)->getId();
        $data = [
            'data' => [
                'type'       => self::ENTITY_TYPE,
                'id'         => (string)$addressId,
                'attributes' => [
                    'types' => [
                        ['addressType' => 'shipping', 'default' => true],
                        null
                    ]
                ]
            ]
        ];

        $response = $this->patch(
            ['entity' => self::ENTITY_TYPE, 'id' => (string)$addressId],
            $data,
            [],
            false
        );

        $this->assertResponseValidationErrors(
            [
                [
                    'title'  => 'form constraint',
                    'detail' => 'This value is mandatory.',
                    'source' => ['pointer' => '/data/attributes/types/1/default']
                ],
                [
                    'title'  => 'form constraint',
                    'detail' => 'This value is mandatory.',
                    'source' => ['pointer' => '/data/attributes/types/1/addressType']
                ],
                [
                    'title'  => 'form constraint',
                    'detail' => 'This value is not valid.',
                    'source' => ['pointer' => '/data/attributes/types/1/addressType']
                ]
            ],
            $response
        );
    }

    public function testTryToUpdateTypesWhenOneOfTypeEmptyString()
    {
        $addressId = $this->getReference(self::BILLING_ADDRESS_REF)->getId();
        $data = [
            'data' => [
                'type'       => self::ENTITY_TYPE,
                'id'         => (string)$addressId,
                'attributes' => [
                    'types' => [
                        ['addressType' => 'shipping', 'default' => true],
                        ''
                    ]
                ]
            ]
        ];

        $response = $this->patch(
            ['entity' => self::ENTITY_TYPE, 'id' => (string)$addressId],
            $data,
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'form constraint',
                'detail' => 'This value is not valid.',
                'source' => ['pointer' => '/data/attributes/types/1']
            ],
            $response
        );
    }

    public function testTryToUpdateTypesWhenOneOfTypeIsEmptyArray()
    {
        $addressId = $this->getReference(self::BILLING_ADDRESS_REF)->getId();
        $data = [
            'data' => [
                'type'       => self::ENTITY_TYPE,
                'id'         => (string)$addressId,
                'attributes' => [
                    'types' => [
                        ['addressType' => 'shipping', 'default' => true],
                        []
                    ]
                ]
            ]
        ];

        $response = $this->patch(
            ['entity' => self::ENTITY_TYPE, 'id' => (string)$addressId],
            $data,
            [],
            false
        );

        $this->assertResponseValidationErrors(
            [
                [
                    'title'  => 'form constraint',
                    'detail' => 'This value is mandatory.',
                    'source' => ['pointer' => '/data/attributes/types/1/default']
                ],
                [
                    'title'  => 'form constraint',
                    'detail' => 'This value is mandatory.',
                    'source' => ['pointer' => '/data/attributes/types/1/addressType']
                ],
                [
                    'title'  => 'form constraint',
                    'detail' => 'This value is not valid.',
                    'source' => ['pointer' => '/data/attributes/types/1/addressType']
                ]
            ],
            $response
        );
    }

    public function testTryToUpdateTypesWhenAddressTypePropertyForOneOfTypeIsNull()
    {
        $addressId = $this->getReference(self::BILLING_ADDRESS_REF)->getId();
        $data = [
            'data' => [
                'type'       => self::ENTITY_TYPE,
                'id'         => (string)$addressId,
                'attributes' => [
                    'types' => [
                        ['addressType' => null, 'default' => false]
                    ]
                ]
            ]
        ];

        $response = $this->patch(
            ['entity' => self::ENTITY_TYPE, 'id' => (string)$addressId],
            $data,
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'form constraint',
                'detail' => 'This value is not valid.',
                'source' => ['pointer' => '/data/attributes/types/0/addressType']
            ],
            $response
        );
    }

    public function testTryToUpdateTypesWhenAddressTypePropertyForOneOfTypeIsEmptyString()
    {
        $addressId = $this->getReference(self::BILLING_ADDRESS_REF)->getId();
        $data = [
            'data' => [
                'type'       => self::ENTITY_TYPE,
                'id'         => (string)$addressId,
                'attributes' => [
                    'types' => [
                        ['addressType' => '', 'default' => false]
                    ]
                ]
            ]
        ];

        $response = $this->patch(
            ['entity' => self::ENTITY_TYPE, 'id' => (string)$addressId],
            $data,
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'form constraint',
                'detail' => 'This value is not valid.',
                'source' => ['pointer' => '/data/attributes/types/0/addressType']
            ],
            $response
        );
    }

    public function testTryToUpdateTypesWhenDefaultOptionForOneOfTypeIsNotBool()
    {
        $addressId = $this->getReference(self::BILLING_ADDRESS_REF)->getId();
        $data = [
            'data' => [
                'type'       => self::ENTITY_TYPE,
                'id'         => (string)$addressId,
                'attributes' => [
                    'types' => [
                        ['addressType' => 'shipping', 'default' => 'test']
                    ]
                ]
            ]
        ];

        $response = $this->patch(
            ['entity' => self::ENTITY_TYPE, 'id' => (string)$addressId],
            $data,
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'form constraint',
                'detail' => 'This value is not valid.',
                'source' => ['pointer' => '/data/attributes/types/0/default']
            ],
            $response
        );
    }

    public function testTryToUpdateTypesWhenDefaultOptionForOneOfTypeIsMissing()
    {
        $addressId = $this->getReference(self::BILLING_ADDRESS_REF)->getId();
        $data = [
            'data' => [
                'type'       => self::ENTITY_TYPE,
                'id'         => (string)$addressId,
                'attributes' => [
                    'types' => [
                        ['addressType' => 'shipping']
                    ]
                ]
            ]
        ];

        $response = $this->patch(
            ['entity' => self::ENTITY_TYPE, 'id' => (string)$addressId],
            $data,
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'form constraint',
                'detail' => 'This value is mandatory.',
                'source' => ['pointer' => '/data/attributes/types/0/default']
            ],
            $response
        );
    }

    public function testTryToUpdateTypesWhenAddressTypePropertyForOneOfTypeIsNotExistingAddressType()
    {
        $addressId = $this->getReference(self::BILLING_ADDRESS_REF)->getId();
        $data = [
            'data' => [
                'type'       => self::ENTITY_TYPE,
                'id'         => (string)$addressId,
                'attributes' => [
                    'types' => [
                        ['addressType' => 'test', 'default' => true]
                    ]
                ]
            ]
        ];

        $response = $this->patch(
            ['entity' => self::ENTITY_TYPE, 'id' => (string)$addressId],
            $data,
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'form constraint',
                'detail' => 'This value is not valid.',
                'source' => ['pointer' => '/data/attributes/types/0/addressType']
            ],
            $response
        );
    }

    public function testTryToUpdateTypesWhenAddressTypePropertyForOneOfTypeIsMissing()
    {
        $addressId = $this->getReference(self::BILLING_ADDRESS_REF)->getId();
        $data = [
            'data' => [
                'type'       => self::ENTITY_TYPE,
                'id'         => (string)$addressId,
                'attributes' => [
                    'types' => [
                        ['default' => true]
                    ]
                ]
            ]
        ];

        $response = $this->patch(
            ['entity' => self::ENTITY_TYPE, 'id' => (string)$addressId],
            $data,
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'form constraint',
                'detail' => 'This value is mandatory.',
                'source' => ['pointer' => '/data/attributes/types/0/addressType']
            ],
            $response
        );
    }

    public function testCreateOneMoreDefaultAddress()
    {
        /** @var AbstractDefaultTypedAddress $existingAddress */
        $existingAddress = $this->getReference(self::DEFAULT_ADDRESS_REF);
        $existingAddressId = $existingAddress->getId();
        /** @var object $owner */
        $owner = $this->getOwner($existingAddress);
        $ownerId = $owner->getId();

        // guard
        self::assertTrue($existingAddress->hasDefault(AddressType::TYPE_SHIPPING));
        $numberOfDefaultAddresses = 0;
        /** @var AbstractDefaultTypedAddress $addr */
        foreach ($owner->getAddresses() as $addr) {
            if ($addr->hasDefault(AddressType::TYPE_SHIPPING)) {
                $numberOfDefaultAddresses++;
            }
        }
        self::assertEquals(1, $numberOfDefaultAddresses);

        $data = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        $data['data']['relationships'][self::OWNER_RELATIONSHIP]['data'] = [
            'type' => self::OWNER_ENTITY_TYPE,
            'id'   => (string)$ownerId
        ];
        $data['data']['attributes']['types'] = [
            ['default' => true, 'addressType' => AddressType::TYPE_SHIPPING]
        ];
        $response = $this->post(
            ['entity' => self::ENTITY_TYPE],
            $data
        );

        /** @var AbstractDefaultTypedAddress $newAddress */
        $newAddress = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, (int)$this->getResourceId($response));
        self::assertAddressTypes(
            [
                ['addressType' => AddressType::TYPE_SHIPPING, 'default' => true]
            ],
            $newAddress->getAddressTypes()
        );
        /** @var AbstractDefaultTypedAddress $existingAddress */
        $existingAddress = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, $existingAddressId);
        self::assertAddressTypes(
            [
                ['addressType' => AddressType::TYPE_BILLING, 'default' => false],
                ['addressType' => AddressType::TYPE_SHIPPING, 'default' => false]
            ],
            $existingAddress->getAddressTypes()
        );
    }

    public function testCreateOneMoreDefaultAddressViaOwnerEntityUpdateResource()
    {
        if (!$this->isActionEnabled($this->getEntityClass(self::OWNER_ENTITY_TYPE), ApiAction::UPDATE)) {
            self::markTestSkipped('The "update" action is disabled for owner entity');
        }

        /** @var AbstractDefaultTypedAddress $existingAddress */
        $existingAddress = $this->getReference(self::DEFAULT_ADDRESS_REF);
        $existingAddressId = $existingAddress->getId();
        /** @var object $owner */
        $owner = $this->getOwner($existingAddress);
        $ownerId = $owner->getId();

        // guard
        self::assertTrue($existingAddress->hasDefault(AddressType::TYPE_SHIPPING));
        $numberOfDefaultAddresses = 0;
        /** @var AbstractDefaultTypedAddress $addr */
        foreach ($owner->getAddresses() as $addr) {
            if ($addr->hasDefault(AddressType::TYPE_SHIPPING)) {
                $numberOfDefaultAddresses++;
            }
        }
        self::assertEquals(1, $numberOfDefaultAddresses);

        $addressData = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        $addressData['data']['id'] = 'new_address';
        $addressData['data']['relationships'][self::OWNER_RELATIONSHIP]['data'] = [
            'type' => self::OWNER_ENTITY_TYPE,
            'id'   => (string)$ownerId
        ];
        $addressData['data']['attributes']['types'] = [
            ['default' => true, 'addressType' => AddressType::TYPE_SHIPPING]
        ];
        $data = [
            'data'     => [
                'type'          => self::OWNER_ENTITY_TYPE,
                'id'            => (string)$ownerId,
                'relationships' => [
                    'addresses' => [
                        'data' => [
                            ['type' => self::ENTITY_TYPE, 'id' => (string)$existingAddressId],
                            ['type' => self::ENTITY_TYPE, 'id' => 'new_address']
                        ]
                    ]
                ]
            ],
            'included' => [
                $addressData['data']
            ]
        ];
        $response = $this->patch(
            ['entity' => self::OWNER_ENTITY_TYPE, 'id' => (string)$ownerId],
            $data
        );

        /** @var AbstractDefaultTypedAddress $newAddress */
        $newAddress = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, self::getNewResourceIdFromIncludedSection($response, 'new_address'));
        self::assertAddressTypes(
            [
                ['addressType' => AddressType::TYPE_SHIPPING, 'default' => true]
            ],
            $newAddress->getAddressTypes()
        );
        /** @var AbstractDefaultTypedAddress $existingAddress */
        $existingAddress = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, $existingAddressId);
        self::assertAddressTypes(
            [
                ['addressType' => AddressType::TYPE_BILLING, 'default' => false],
                ['addressType' => AddressType::TYPE_SHIPPING, 'default' => false]
            ],
            $existingAddress->getAddressTypes()
        );
    }

    public function testCreateSeveralDefaultAddressesWithOwnerEntityRelationshipViaOwnerEntityUpdateResource()
    {
        if (!$this->isActionEnabled($this->getEntityClass(self::OWNER_ENTITY_TYPE), ApiAction::UPDATE)) {
            self::markTestSkipped('The "update" action is disabled for owner entity');
        }

        /** @var object $owner */
        $owner = $this->getOwner($this->getReference(self::DEFAULT_ADDRESS_REF));
        $ownerId = $owner->getId();
        $owner->getAddresses()->clear();
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        // guard
        self::assertCount(0, $owner->getAddresses());

        $addressData1 = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        $addressData1['data']['id'] = 'new_address1';
        $addressData1['data']['relationships'][self::OWNER_RELATIONSHIP]['data'] = [
            'type' => self::OWNER_ENTITY_TYPE,
            'id'   => (string)$ownerId
        ];
        $addressData1['data']['attributes']['types'] = [
            ['default' => true, 'addressType' => AddressType::TYPE_SHIPPING]
        ];
        $addressData2 = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        $addressData2['data']['id'] = 'new_address2';
        $addressData2['data']['relationships'][self::OWNER_RELATIONSHIP]['data'] = [
            'type' => self::OWNER_ENTITY_TYPE,
            'id'   => (string)$ownerId
        ];
        $addressData2['data']['attributes']['types'] = [
            ['default' => true, 'addressType' => AddressType::TYPE_SHIPPING]
        ];
        $data = [
            'data'     => [
                'type'          => self::OWNER_ENTITY_TYPE,
                'id'            => (string)$ownerId,
                'relationships' => [
                    'addresses' => [
                        'data' => [
                            ['type' => self::ENTITY_TYPE, 'id' => 'new_address1'],
                            ['type' => self::ENTITY_TYPE, 'id' => 'new_address2']
                        ]
                    ]
                ]
            ],
            'included' => [
                $addressData1['data'],
                $addressData2['data']
            ]
        ];
        $response = $this->patch(
            ['entity' => self::OWNER_ENTITY_TYPE, 'id' => (string)$ownerId],
            $data
        );

        /** @var AbstractDefaultTypedAddress $newAddress1 */
        $newAddress1 = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, self::getNewResourceIdFromIncludedSection($response, 'new_address1'));
        self::assertAddressTypes(
            [
                ['addressType' => AddressType::TYPE_SHIPPING, 'default' => true]
            ],
            $newAddress1->getAddressTypes()
        );
        /** @var AbstractDefaultTypedAddress $newAddress2 */
        $newAddress2 = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, self::getNewResourceIdFromIncludedSection($response, 'new_address2'));
        self::assertAddressTypes(
            [
                ['addressType' => AddressType::TYPE_SHIPPING, 'default' => false]
            ],
            $newAddress2->getAddressTypes()
        );
    }

    public function testCreateSeveralDefaultAddressesWithoutOwnerEntityRelationshipViaOwnerEntityUpdateResource()
    {
        if (!$this->isActionEnabled($this->getEntityClass(self::OWNER_ENTITY_TYPE), ApiAction::UPDATE)) {
            self::markTestSkipped('The "update" action is disabled for owner entity');
        }

        /** @var object $owner */
        $owner = $this->getOwner($this->getReference(self::DEFAULT_ADDRESS_REF));
        $ownerId = $owner->getId();
        $owner->getAddresses()->clear();
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        // guard
        self::assertCount(0, $owner->getAddresses());

        $addressData1 = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        $addressData1['data']['id'] = 'new_address1';
        unset($addressData1['data']['relationships'][self::OWNER_RELATIONSHIP]);
        $addressData1['data']['attributes']['types'] = [
            ['default' => true, 'addressType' => AddressType::TYPE_SHIPPING]
        ];
        $addressData2 = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        $addressData2['data']['id'] = 'new_address2';
        unset($addressData2['data']['relationships'][self::OWNER_RELATIONSHIP]);
        $addressData2['data']['attributes']['types'] = [
            ['default' => true, 'addressType' => AddressType::TYPE_SHIPPING]
        ];
        $data = [
            'data'     => [
                'type'          => self::OWNER_ENTITY_TYPE,
                'id'            => (string)$ownerId,
                'relationships' => [
                    'addresses' => [
                        'data' => [
                            ['type' => self::ENTITY_TYPE, 'id' => 'new_address1'],
                            ['type' => self::ENTITY_TYPE, 'id' => 'new_address2']
                        ]
                    ]
                ]
            ],
            'included' => [
                $addressData1['data'],
                $addressData2['data']
            ]
        ];
        $response = $this->patch(
            ['entity' => self::OWNER_ENTITY_TYPE, 'id' => (string)$ownerId],
            $data
        );

        /** @var AbstractDefaultTypedAddress $newAddress1 */
        $newAddress1 = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, self::getNewResourceIdFromIncludedSection($response, 'new_address1'));
        self::assertAddressTypes(
            [
                ['addressType' => AddressType::TYPE_SHIPPING, 'default' => true]
            ],
            $newAddress1->getAddressTypes()
        );
        /** @var AbstractDefaultTypedAddress $newAddress2 */
        $newAddress2 = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, self::getNewResourceIdFromIncludedSection($response, 'new_address2'));
        self::assertAddressTypes(
            [
                ['addressType' => AddressType::TYPE_SHIPPING, 'default' => false]
            ],
            $newAddress2->getAddressTypes()
        );
    }
}
