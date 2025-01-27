<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\Tests\Functional\Form\Type\AddressValidation\Frontend;

use Oro\Bundle\AddressValidationBundle\Form\Type\AddressBookAwareAddressValidationResultType;
use Oro\Bundle\AddressValidationBundle\Form\Type\Frontend\FrontendAddressBookAwareAddressValidationResultType;
use Oro\Bundle\AddressValidationBundle\ResolvedAddress\ResolvedAddressAcceptorInterface;
use Oro\Bundle\AddressValidationBundle\Test\ResolvedAddressAwareTestTrait;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserAddresses;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\CustomerBundle\Utils\AddressBookAddressUtils;
use Oro\Bundle\FrontendTestFrameworkBundle\Test\FrontendWebTestCase;
use Oro\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrderAddressData;
use Oro\Bundle\TestFrameworkBundle\Test\Form\FormAwareTestTrait;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * @dbIsolationPerTest
 */
class FrontendAddressBookAwareAddressValidationResultTypeTest extends FrontendWebTestCase
{
    use FormAwareTestTrait;
    use ResolvedAddressAwareTestTrait;

    private ResolvedAddressAcceptorInterface $resolvedAddressAcceptor;

    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures(
            [
                LoadOrderAddressData::class,
                LoadCustomerUserAddresses::class,
                LoadCustomerUserData::class,
            ]
        );

        $this->resolvedAddressAcceptor = self::getContainer()->get('oro_address_validation.resolved_address.acceptor');
    }

    public function testHasAddressField(): void
    {
        $originalAddress = $this->getReference(LoadOrderAddressData::ORDER_ADDRESS_1);
        $suggestedAddresses = [
            self::createResolvedAddress($this->getReference(LoadOrderAddressData::ORDER_ADDRESS_2), $originalAddress),
        ];

        $form = self::createForm(
            FrontendAddressBookAwareAddressValidationResultType::class,
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

    public function testHasUpdateAddressField(): void
    {
        $this->loginUser(LoadCustomerUserData::EMAIL);
        $this->updateCustomerUserSecurityToken(LoadCustomerUserData::EMAIL);

        $orderAddress = $this->getReference(LoadOrderAddressData::ORDER_ADDRESS_1);
        AddressBookAddressUtils::setAddressBookAddress(
            $orderAddress,
            $this->getReference('grzegorz.brzeczyszczykiewicz@example.com.address_1')
        );
        $suggestedAddresses = [
            self::createResolvedAddress($this->getReference(LoadOrderAddressData::ORDER_ADDRESS_2), $orderAddress),
        ];

        $form = self::createForm(
            FrontendAddressBookAwareAddressValidationResultType::class,
            null,
            [
                'original_address' => $orderAddress,
                'suggested_addresses' => $suggestedAddresses,
                'allow_update_address' => true,
            ]
        );

        self::assertFormHasField($form, 'update_address', CheckboxType::class);

        AddressBookAddressUtils::resetAddressBookAddress($orderAddress);
    }

    public function testHasNoUpdateAddressFieldWhenNoAddressBookAddress(): void
    {
        $this->loginUser(LoadCustomerUserData::EMAIL);
        $this->updateCustomerUserSecurityToken(LoadCustomerUserData::EMAIL);

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
        $this->loginUser(LoadCustomerUserData::EMAIL);
        $this->updateCustomerUserSecurityToken(LoadCustomerUserData::EMAIL);

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
            FrontendAddressBookAwareAddressValidationResultType::class,
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

    public function testSubmitWithUpdateAddress(): void
    {
        $originalAddress = $this->getReference(LoadOrderAddressData::ORDER_ADDRESS_1);
        $suggestedAddress = self::createResolvedAddress(
            $this->getReference(LoadOrderAddressData::ORDER_ADDRESS_2),
            $originalAddress
        );

        $form = self::createForm(
            FrontendAddressBookAwareAddressValidationResultType::class,
            ['address' => $originalAddress],
            [
                'original_address' => $originalAddress,
                'suggested_addresses' => [$suggestedAddress],
                'allow_update_address' => true,
            ]
        );

        self::assertEquals(['address' => $originalAddress], $form->getData());

        $form->submit(['address' => '1', 'update_address' => '1']);

        self::assertTrue($form->isValid());
        self::assertTrue($form->getData()['update_address']);

        self::assertEquals($suggestedAddress->getCountry(), $form->getData()['address']->getCountry());
        self::assertEquals($suggestedAddress->getRegion(), $form->getData()['address']->getRegion());
        self::assertEquals($suggestedAddress->getCity(), $form->getData()['address']->getCity());
        self::assertEquals($suggestedAddress->getStreet(), $form->getData()['address']->getStreet());
        self::assertEquals($suggestedAddress->getStreet2(), $form->getData()['address']->getStreet2());
        self::assertEquals($suggestedAddress->getPostalCode(), $form->getData()['address']->getPostalCode());
    }

    public function testGetBlockPrefix(): void
    {
        $originalAddress = $this->getReference(LoadOrderAddressData::ORDER_ADDRESS_1);
        $suggestedAddresses = [
            self::createResolvedAddress($this->getReference(LoadOrderAddressData::ORDER_ADDRESS_2), $originalAddress),
        ];

        $form = self::createForm(
            FrontendAddressBookAwareAddressValidationResultType::class,
            ['address' => $originalAddress],
            [
                'original_address' => $originalAddress,
                'suggested_addresses' => $suggestedAddresses,
            ]
        );

        self::assertContains(
            'oro_address_validation_frontend_address_book_aware_validation_result',
            $form->createView()->vars['block_prefixes']
        );
    }
}
