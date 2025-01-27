<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\Tests\Functional\Form\Type\AddressValidation;

use Oro\Bundle\AddressValidationBundle\Form\Type\AddressBookAwareAddressValidationResultType;
use Oro\Bundle\AddressValidationBundle\ResolvedAddress\ResolvedAddressAcceptorInterface;
use Oro\Bundle\AddressValidationBundle\Test\ResolvedAddressAwareTestTrait;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserAddresses;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\CustomerBundle\Utils\AddressBookAddressUtils;
use Oro\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrderAddressData;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Test\Functional\RolePermissionExtension;
use Oro\Bundle\TestFrameworkBundle\Test\Form\FormAwareTestTrait;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @dbIsolationPerTest
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class AddressBookAwareAddressValidationResultTypeTest extends WebTestCase
{
    use FormAwareTestTrait;
    use ResolvedAddressAwareTestTrait;
    use RolePermissionExtension;

    private ResolvedAddressAcceptorInterface $resolvedAddressAcceptor;

    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadOrderAddressData::class, LoadCustomerUserAddresses::class]);

        $this->resolvedAddressAcceptor = self::getContainer()->get('oro_address_validation.resolved_address.acceptor');
    }

    public function testHasAddressField(): void
    {
        $originalAddress = $this->getReference(LoadOrderAddressData::ORDER_ADDRESS_1);
        $suggestedAddresses = [
            self::createResolvedAddress($this->getReference(LoadOrderAddressData::ORDER_ADDRESS_2), $originalAddress),
        ];

        $form = self::createForm(
            AddressBookAwareAddressValidationResultType::class,
            null,
            [
                'original_address' => $originalAddress,
                'suggested_addresses' => $suggestedAddresses,
            ]
        );

        self::assertNull($form->getConfig()->getOption('data_class'));
        self::assertSame([], $form->getConfig()->getOption('validation_groups'));

        self::assertFormHasField(
            $form,
            'address',
            ChoiceType::class,
            [
                'expanded' => true,
                'multiple' => false,
                'inherit_data' => false,
                'choices' => [$originalAddress, ...$suggestedAddresses],
            ]
        );
    }

    public function testHasCreateAddressFieldWhenCustomerUserPassed(): void
    {
        $this->loginUser(self::AUTH_USER);
        $this->updateUserSecurityToken(self::AUTH_USER);

        $originalAddress = $this->getReference(LoadOrderAddressData::ORDER_ADDRESS_1);
        $suggestedAddresses = [
            self::createResolvedAddress($this->getReference(LoadOrderAddressData::ORDER_ADDRESS_2), $originalAddress),
        ];
        $customerUser = $this->getReference(LoadCustomerUserData::EMAIL);

        $form = self::createForm(
            AddressBookAwareAddressValidationResultType::class,
            null,
            [
                'original_address' => $originalAddress,
                'suggested_addresses' => $suggestedAddresses,
                'customer_user' => $customerUser,
            ]
        );

        self::assertEquals(CustomerUserAddress::class, $form->getConfig()->getOption('address_book_new_address_class'));

        self::assertFormHasField($form, 'create_address', CheckboxType::class);
    }

    public function testHasCreateAddressFieldWhenCustomerPassed(): void
    {
        $this->loginUser(self::AUTH_USER);
        $this->updateUserSecurityToken(self::AUTH_USER);

        $originalAddress = $this->getReference(LoadOrderAddressData::ORDER_ADDRESS_1);
        $suggestedAddresses = [
            self::createResolvedAddress($this->getReference(LoadOrderAddressData::ORDER_ADDRESS_2), $originalAddress),
        ];
        $customerUser = $this->getReference(LoadCustomerUserData::EMAIL);

        $form = self::createForm(
            AddressBookAwareAddressValidationResultType::class,
            null,
            [
                'original_address' => $originalAddress,
                'suggested_addresses' => $suggestedAddresses,
                'customer' => $customerUser->getCustomer(),
            ]
        );

        self::assertEquals(CustomerAddress::class, $form->getConfig()->getOption('address_book_new_address_class'));

        self::assertFormHasField($form, 'create_address', CheckboxType::class);
    }

    public function testHasNoCreateAddressFieldWhenExplicitlyNotAllowed(): void
    {
        $this->loginUser(self::AUTH_USER);
        $this->updateUserSecurityToken(self::AUTH_USER);

        $originalAddress = $this->getReference(LoadOrderAddressData::ORDER_ADDRESS_1);
        $suggestedAddresses = [
            self::createResolvedAddress($this->getReference(LoadOrderAddressData::ORDER_ADDRESS_2), $originalAddress),
        ];
        $customerUser = $this->getReference(LoadCustomerUserData::EMAIL);

        $form = self::createForm(
            AddressBookAwareAddressValidationResultType::class,
            null,
            [
                'original_address' => $originalAddress,
                'suggested_addresses' => $suggestedAddresses,
                'allow_create_address' => false,
                'customer_user' => $customerUser,
                'customer' => $customerUser->getCustomer(),
            ]
        );

        self::assertFalse($form->has('create_address'));
    }

    public function testHasNoCreateAddressFieldWhenNoCustomerUserAndCustomer(): void
    {
        $this->loginUser(self::AUTH_USER);
        $this->updateUserSecurityToken(self::AUTH_USER);

        $originalAddress = $this->getReference(LoadOrderAddressData::ORDER_ADDRESS_1);
        $suggestedAddresses = [
            self::createResolvedAddress($this->getReference(LoadOrderAddressData::ORDER_ADDRESS_2), $originalAddress),
        ];

        $form = self::createForm(
            AddressBookAwareAddressValidationResultType::class,
            null,
            [
                'original_address' => $originalAddress,
                'suggested_addresses' => $suggestedAddresses,
            ]
        );

        self::assertNull($form->getConfig()->getOption('address_book_new_address_class'));

        self::assertFalse($form->has('create_address'));
    }

    public function testHasNoCreateAddressFieldWhenNoAddressBookNewAddressClass(): void
    {
        $this->loginUser(self::AUTH_USER);
        $this->updateUserSecurityToken(self::AUTH_USER);

        $originalAddress = $this->getReference(LoadOrderAddressData::ORDER_ADDRESS_1);
        $suggestedAddresses = [
            self::createResolvedAddress($this->getReference(LoadOrderAddressData::ORDER_ADDRESS_2), $originalAddress),
        ];
        $customerUser = $this->getReference(LoadCustomerUserData::EMAIL);

        $form = self::createForm(
            AddressBookAwareAddressValidationResultType::class,
            null,
            [
                'original_address' => $originalAddress,
                'suggested_addresses' => $suggestedAddresses,
                'customer_user' => $customerUser,
                'address_book_new_address_class' => null,
            ]
        );

        self::assertFalse($form->has('create_address'));
    }

    public function testHasNoCreateAddressFieldWhenUpdateAllowed(): void
    {
        $this->loginUser(self::AUTH_USER);
        $this->updateUserSecurityToken(self::AUTH_USER);

        $originalAddress = $this->getReference(LoadOrderAddressData::ORDER_ADDRESS_1);
        $suggestedAddresses = [
            self::createResolvedAddress($this->getReference(LoadOrderAddressData::ORDER_ADDRESS_2), $originalAddress),
        ];
        $customerUser = $this->getReference(LoadCustomerUserData::EMAIL);

        $form = self::createForm(
            AddressBookAwareAddressValidationResultType::class,
            null,
            [
                'original_address' => $originalAddress,
                'suggested_addresses' => $suggestedAddresses,
                'allow_update_address' => true,
                'customer_user' => $customerUser,
            ]
        );

        self::assertFalse($form->has('create_address'));
        self::asserttrue($form->has('update_address'));
    }

    public function testHasNoCreateAddressFieldWhenNotGranted(): void
    {
        $originalAddress = $this->getReference(LoadOrderAddressData::ORDER_ADDRESS_1);
        $suggestedAddresses = [
            self::createResolvedAddress($this->getReference(LoadOrderAddressData::ORDER_ADDRESS_2), $originalAddress),
        ];
        $customerUser = $this->getReference(LoadCustomerUserData::EMAIL);

        $form = self::createForm(
            AddressBookAwareAddressValidationResultType::class,
            null,
            [
                'original_address' => $originalAddress,
                'suggested_addresses' => $suggestedAddresses,
                'customer_user' => $customerUser,
               'address_book_new_address_class' => CustomerUserAddress::class,
            ]
        );

        self::assertFalse($form->has('create_address'));
    }

    public function testHasNoCreateAddressFieldWhenCustomerUserCreateNotAllowed(): void
    {
        $this->loginUser(self::AUTH_USER);
        $this->updateUserSecurityToken(self::AUTH_USER);

        self::updateRolePermission(
            User::ROLE_ADMINISTRATOR,
            CustomerUserAddress::class,
            AccessLevel::NONE_LEVEL,
            'CREATE'
        );

        $originalAddress = $this->getReference(LoadOrderAddressData::ORDER_ADDRESS_1);
        $suggestedAddresses = [
            self::createResolvedAddress($this->getReference(LoadOrderAddressData::ORDER_ADDRESS_2), $originalAddress),
        ];
        $customerUser = $this->getReference(LoadCustomerUserData::EMAIL);

        $form = self::createForm(
            AddressBookAwareAddressValidationResultType::class,
            null,
            [
                'original_address' => $originalAddress,
                'suggested_addresses' => $suggestedAddresses,
                'customer_user' => $customerUser
            ]
        );

        self::assertFalse($form->has('create_address'));
    }

    public function testHasUpdateAddressField(): void
    {
        $this->loginUser(self::AUTH_USER);
        $this->updateUserSecurityToken(self::AUTH_USER);

        $orderAddress = $this->getReference(LoadOrderAddressData::ORDER_ADDRESS_1);
        AddressBookAddressUtils::setAddressBookAddress(
            $orderAddress,
            $this->getReference('grzegorz.brzeczyszczykiewicz@example.com.address_1')
        );
        $suggestedAddresses = [
            self::createResolvedAddress($this->getReference(LoadOrderAddressData::ORDER_ADDRESS_2), $orderAddress),
        ];

        $form = self::createForm(
            AddressBookAwareAddressValidationResultType::class,
            null,
            [
                'original_address' => $orderAddress,
                'suggested_addresses' => $suggestedAddresses,
            ]
        );

        self::assertFormHasField($form, 'update_address', CheckboxType::class);

        AddressBookAddressUtils::resetAddressBookAddress($orderAddress);
    }

    public function testHasNoUpdateAddressFieldWhenNoAddressBookAddress(): void
    {
        $this->loginUser(self::AUTH_USER);
        $this->updateUserSecurityToken(self::AUTH_USER);

        $originalAddress = $this->getReference(LoadOrderAddressData::ORDER_ADDRESS_1);
        $suggestedAddresses = [
            self::createResolvedAddress($this->getReference(LoadOrderAddressData::ORDER_ADDRESS_2), $originalAddress),
        ];

        $form = self::createForm(
            AddressBookAwareAddressValidationResultType::class,
            null,
            [
                'original_address' => $originalAddress,
                'suggested_addresses' => $suggestedAddresses,
            ]
        );

        self::assertFalse($form->has('update_address'));
    }

    public function testHasNoUpdateAddressFieldWhenExplicitlyNotAllowed(): void
    {
        $this->loginUser(self::AUTH_USER);
        $this->updateUserSecurityToken(self::AUTH_USER);

        $originalAddress = $this->getReference(LoadOrderAddressData::ORDER_ADDRESS_1);
        $suggestedAddresses = [
            self::createResolvedAddress($this->getReference(LoadOrderAddressData::ORDER_ADDRESS_2), $originalAddress),
        ];

        $form = self::createForm(
            AddressBookAwareAddressValidationResultType::class,
            null,
            [
                'original_address' => $originalAddress,
                'suggested_addresses' => $suggestedAddresses,
                'allow_update_address' => false,
            ]
        );

        self::assertFalse($form->has('update_address'));
    }

    public function testHasNoUpdateAddressFieldWhenNotGranted(): void
    {
        self::resetClient();
        $this->initClient();

        $originalAddress = $this->getReference(LoadOrderAddressData::ORDER_ADDRESS_1);
        $suggestedAddresses = [
            self::createResolvedAddress($this->getReference(LoadOrderAddressData::ORDER_ADDRESS_2), $originalAddress),
        ];

        $form = self::createForm(
            AddressBookAwareAddressValidationResultType::class,
            null,
            [
                'original_address' => $originalAddress,
                'suggested_addresses' => $suggestedAddresses,
            ]
        );

        self::assertFalse($form->has('update_address'));
    }

    public function testSubmit(): void
    {
        $originalAddress = $this->getReference(LoadOrderAddressData::ORDER_ADDRESS_1);
        $suggestedAddresses = [
            self::createResolvedAddress($this->getReference(LoadOrderAddressData::ORDER_ADDRESS_2), $originalAddress),
        ];

        $form = self::createForm(
            AddressBookAwareAddressValidationResultType::class,
            ['address' => $originalAddress],
            [
                'original_address' => $originalAddress,
                'suggested_addresses' => $suggestedAddresses,
            ]
        );

        self::assertEquals(['address' => $originalAddress], $form->getData());

        $form->submit(['address' => '1']);

        self::assertTrue($form->isValid());

        $expectedAddress = $this->resolvedAddressAcceptor->acceptResolvedAddress($suggestedAddresses[0]);
        self::assertEquals(['address' => $expectedAddress], $form->getData());
    }

    public function testSubmitWithCreateAddress(): void
    {
        $originalAddress = $this->getReference(LoadOrderAddressData::ORDER_ADDRESS_1);
        $suggestedAddresses = [
            self::createResolvedAddress($this->getReference(LoadOrderAddressData::ORDER_ADDRESS_2), $originalAddress),
        ];

        $form = self::createForm(
            AddressBookAwareAddressValidationResultType::class,
            ['address' => $originalAddress],
            [
                'original_address' => $originalAddress,
                'suggested_addresses' => $suggestedAddresses,
                'allow_create_address' => true,
            ]
        );

        self::assertEquals(['address' => $originalAddress], $form->getData());

        $form->submit(['address' => '1', 'create_address' => '1']);

        self::assertTrue($form->isValid());

        $expectedAddress = $this->resolvedAddressAcceptor->acceptResolvedAddress($suggestedAddresses[0]);
        self::assertEquals(['address' => $expectedAddress, 'create_address' => true], $form->getData());
    }

    public function testSubmitWithUpdateAddress(): void
    {
        $propertyAccessor = new PropertyAccessor();
        $addressFields = self::getContainer()->getParameter('oro_address_validation.address_validation_fields');
        $originalAddress = $this->getReference(LoadOrderAddressData::ORDER_ADDRESS_1);
        $suggestedAddresses = [
            self::createResolvedAddress($this->getReference(LoadOrderAddressData::ORDER_ADDRESS_2), $originalAddress),
        ];

        $form = self::createForm(
            AddressBookAwareAddressValidationResultType::class,
            ['address' => $originalAddress],
            [
                'original_address' => $originalAddress,
                'suggested_addresses' => $suggestedAddresses,
                'allow_update_address' => true,
            ]
        );

        self::assertEquals(['address' => $originalAddress], $form->getData());

        $form->submit(['address' => '1', 'update_address' => '1']);

        self::assertTrue($form->isValid());

        $expectedAddress = $this->resolvedAddressAcceptor->acceptResolvedAddress($suggestedAddresses[0]);

        $formData = $form->getData();
        self::assertTrue($formData['update_address']);

        foreach ($addressFields as $addressField) {
            self::assertEquals(
                $propertyAccessor->getValue($expectedAddress, $addressField),
                $propertyAccessor->getValue($formData['address'], $addressField)
            );
        }
    }

    public function testGetBlockPrefix(): void
    {
        $originalAddress = $this->getReference(LoadOrderAddressData::ORDER_ADDRESS_1);
        $suggestedAddresses = [
            self::createResolvedAddress($this->getReference(LoadOrderAddressData::ORDER_ADDRESS_2), $originalAddress),
        ];

        $form = self::createForm(
            AddressBookAwareAddressValidationResultType::class,
            ['address' => $originalAddress],
            [
                'original_address' => $originalAddress,
                'suggested_addresses' => $suggestedAddresses,
            ]
        );

        self::assertContains(
            'oro_address_validation_address_book_aware_validation_result',
            $form->createView()->vars['block_prefixes']
        );
    }
}
