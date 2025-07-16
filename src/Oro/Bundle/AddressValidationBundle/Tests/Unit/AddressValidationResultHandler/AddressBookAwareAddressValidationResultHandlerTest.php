<?php

namespace Oro\Bundle\AddressValidationBundle\Tests\Unit\AddressValidationResultHandler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\AddressValidationBundle\AddressValidationResultHandler\AddressBookAwareAddressValidationResultHandler;
use Oro\Bundle\AddressValidationBundle\Model\AddressValidatedAtAwareInterface;
use Oro\Bundle\AddressValidationBundle\Tests\Unit\Stub\AddressValidatedAtAwareStub;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Utils\AddressBookAddressUtils;
use Oro\Bundle\CustomerBundle\Utils\AddressCopier;
use Oro\Bundle\OrderBundle\Entity\OrderAddress;
use Oro\Bundle\SecurityBundle\Acl\BasicPermission;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class AddressBookAwareAddressValidationResultHandlerTest extends TestCase
{
    private AuthorizationCheckerInterface&MockObject $authorizationChecker;
    private AddressCopier&MockObject $addressCopier;
    private AddressBookAwareAddressValidationResultHandler $handler;
    private EntityManagerInterface&MockObject $entityManager;
    private AddressType $shippingAddressType;

    #[\Override]
    protected function setUp(): void
    {
        $doctrine = $this->createMock(ManagerRegistry::class);
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->addressCopier = $this->createMock(AddressCopier::class);

        $this->handler = new AddressBookAwareAddressValidationResultHandler(
            $doctrine,
            $this->authorizationChecker,
            $this->addressCopier,
            AddressType::TYPE_SHIPPING
        );

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $doctrine->expects(self::any())
            ->method('getManagerForClass')
            ->willReturn($this->entityManager);

        $this->shippingAddressType = new AddressType(AddressType::TYPE_SHIPPING);
        $this->entityManager->expects(self::any())
            ->method('getReference')
            ->with(AddressType::class, AddressType::TYPE_SHIPPING)
            ->willReturn($this->shippingAddressType);
    }

    public function testDoesNothingWhenFormNotValidAndNoSuggestions(): void
    {
        $request = new Request();
        $originalAddress = new OrderAddress();
        $suggestedAddresses = [];

        $formConfig = $this->createMock(FormConfigInterface::class);
        $formConfig->expects(self::any())
            ->method('getOption')
            ->willReturnMap([
                ['suggested_addresses', null, $suggestedAddresses],
                ['original_address', null, $originalAddress],
            ]);

        $addressValidationResultForm = $this->createMock(FormInterface::class);
        $addressValidationResultForm->expects(self::any())
            ->method('getConfig')
            ->willReturn($formConfig);
        $addressValidationResultForm->expects(self::once())
            ->method('submit')
            ->with(['address' => '0']);
        $addressValidationResultForm->expects(self::once())
            ->method('isSubmitted')
            ->willReturn(true);
        $addressValidationResultForm->expects(self::once())
            ->method('isValid')
            ->willReturn(false);

        $this->addressCopier->expects(self::never())
            ->method(self::anything());

        $this->entityManager->expects(self::never())
            ->method(self::anything());

        $this->handler->handleAddressValidationRequest($addressValidationResultForm, $request);
    }

    public function testDoesNothingWhenFormNotValidAndHasSuggestions(): void
    {
        $request = new Request();
        $originalAddress = new OrderAddress();
        $suggestedAddresses = [new OrderAddress()];

        $formConfig = $this->createMock(FormConfigInterface::class);
        $formConfig->expects(self::any())
            ->method('getOption')
            ->willReturnMap([
                ['suggested_addresses', null, $suggestedAddresses],
                ['original_address', null, $originalAddress],
            ]);

        $addressValidationResultForm = $this->createMock(FormInterface::class);
        $addressValidationResultForm->expects(self::any())
            ->method('getConfig')
            ->willReturn($formConfig);
        $addressValidationResultForm->expects(self::never())
            ->method('submit');
        $addressValidationResultForm->expects(self::once())
            ->method('handleRequest')
            ->with($request);
        $addressValidationResultForm->expects(self::once())
            ->method('isSubmitted')
            ->willReturn(true);
        $addressValidationResultForm->expects(self::once())
            ->method('isValid')
            ->willReturn(false);

        $this->addressCopier->expects(self::never())
            ->method(self::anything());

        $this->entityManager->expects(self::never())
            ->method(self::anything());

        $this->handler->handleAddressValidationRequest($addressValidationResultForm, $request);
    }

    /**
     * @dataProvider noAddressBookAddressDataProvider
     */
    public function testSetsOnlyValidatedAtWhenNoAddressBookAddress(
        AbstractAddress&AddressValidatedAtAwareInterface $originalAddress
    ): void {
        $request = new Request();
        $selectedAddress = $originalAddress;
        $suggestedAddresses = [];

        $formConfig = $this->createMock(FormConfigInterface::class);
        $formConfig->expects(self::any())
            ->method('getOption')
            ->willReturnMap([
                ['suggested_addresses', null, $suggestedAddresses],
                ['original_address', null, $originalAddress],
            ]);

        $addressValidationResultForm = $this->createMock(FormInterface::class);
        $addressValidationResultForm->expects(self::any())
            ->method('getConfig')
            ->willReturn($formConfig);
        $addressValidationResultForm->expects(self::once())
            ->method('submit')
            ->with(['address' => '0']);
        $addressValidationResultForm->expects(self::never())
            ->method('handleRequest');
        $addressValidationResultForm->expects(self::once())
            ->method('isSubmitted')
            ->willReturn(true);
        $addressValidationResultForm->expects(self::once())
            ->method('isValid')
            ->willReturn(true);
        $addressValidationResultForm->expects(self::once())
            ->method('getData')
            ->willReturn(['address' => $selectedAddress]);

        $this->addressCopier->expects(self::never())
            ->method(self::anything());

        $this->entityManager->expects(self::never())
            ->method(self::anything());

        self::assertNull($selectedAddress->getValidatedAt());

        $this->handler->handleAddressValidationRequest($addressValidationResultForm, $request);

        self::assertInstanceOf(\DateTimeInterface::class, $selectedAddress->getValidatedAt());
    }

    public function noAddressBookAddressDataProvider(): array
    {
        return [
            ['originalAddress' => new AddressValidatedAtAwareStub()],
            ['originalAddress' => new OrderAddress()]
        ];
    }

    /**
     * @dataProvider addressBookAddressDataProvider
     */
    public function testResetsAddressBookAddress(
        CustomerAddress|CustomerUserAddress $addressBookAddress
    ): void {
        $request = new Request();
        $originalAddress = new OrderAddress();
        $selectedAddress = new OrderAddress();
        AddressBookAddressUtils::setAddressBookAddress($selectedAddress, $addressBookAddress);

        $suggestedAddresses = [$selectedAddress];

        $formConfig = $this->createMock(FormConfigInterface::class);
        $formConfig->expects(self::any())
            ->method('getOption')
            ->willReturnMap([
                ['suggested_addresses', null, $suggestedAddresses],
                ['original_address', null, $originalAddress],
            ]);

        $addressValidationResultForm = $this->createMock(FormInterface::class);
        $addressValidationResultForm->expects(self::any())
            ->method('getConfig')
            ->willReturn($formConfig);
        $addressValidationResultForm->expects(self::never())
            ->method('submit');
        $addressValidationResultForm->expects(self::once())
            ->method('handleRequest')
            ->with($request);
        $addressValidationResultForm->expects(self::once())
            ->method('isSubmitted')
            ->willReturn(true);
        $addressValidationResultForm->expects(self::once())
            ->method('isValid')
            ->willReturn(true);
        $addressValidationResultForm->expects(self::once())
            ->method('getData')
            ->willReturn(['address' => $selectedAddress]);

        $this->addressCopier->expects(self::never())
            ->method(self::anything());

        $this->entityManager->expects(self::never())
            ->method(self::anything());

        self::assertNotNull(AddressBookAddressUtils::getAddressBookAddress($selectedAddress));

        $this->handler->handleAddressValidationRequest($addressValidationResultForm, $request);

        self::assertNull(AddressBookAddressUtils::getAddressBookAddress($selectedAddress));
    }

    public function addressBookAddressDataProvider(): array
    {
        return [
            [new CustomerUserAddress()],
            [new CustomerAddress()]
        ];
    }

    public function testNotUpdatesValidatedAtWhenOriginalAddressSelectedAndUpdateIsNotGranted(): void
    {
        $request = new Request();
        $originalAddress = new OrderAddress();
        $selectedAddress = $originalAddress;
        $addressBookAddress = new CustomerUserAddress();
        AddressBookAddressUtils::setAddressBookAddress($originalAddress, $addressBookAddress);
        $suggestedAddresses = [];

        $formConfig = $this->createMock(FormConfigInterface::class);
        $formConfig->expects(self::any())
            ->method('getOption')
            ->willReturnMap([
                ['suggested_addresses', null, $suggestedAddresses],
                ['original_address', null, $originalAddress],
            ]);

        $addressValidationResultForm = $this->createMock(FormInterface::class);
        $addressValidationResultForm->expects(self::any())
            ->method('getConfig')
            ->willReturn($formConfig);
        $addressValidationResultForm->expects(self::once())
            ->method('submit')
            ->with(['address' => '0']);
        $addressValidationResultForm->expects(self::never())
            ->method('handleRequest');
        $addressValidationResultForm->expects(self::once())
            ->method('isSubmitted')
            ->willReturn(true);
        $addressValidationResultForm->expects(self::once())
            ->method('isValid')
            ->willReturn(true);
        $addressValidationResultForm->expects(self::once())
            ->method('getData')
            ->willReturn(['address' => $selectedAddress]);

        $this->authorizationChecker->expects(self::once())
            ->method('isGranted')
            ->with(BasicPermission::EDIT, $addressBookAddress)
            ->willReturn(false);

        $this->addressCopier->expects(self::never())
            ->method(self::anything());

        $this->entityManager->expects(self::never())
            ->method(self::anything());

        self::assertNull($selectedAddress->getValidatedAt());

        $this->handler->handleAddressValidationRequest($addressValidationResultForm, $request);

        self::assertInstanceOf(\DateTimeInterface::class, $selectedAddress->getValidatedAt());
    }

    public function testUpdatesValidatedAtWhenOriginalAddressSelectedAndUpdateIsGranted(): void
    {
        $request = new Request();
        $originalAddress = new OrderAddress();
        $selectedAddress = $originalAddress;
        $addressBookAddress = new CustomerUserAddress();
        AddressBookAddressUtils::setAddressBookAddress($originalAddress, $addressBookAddress);
        $suggestedAddresses = [];

        $formConfig = $this->createMock(FormConfigInterface::class);
        $formConfig->expects(self::any())
            ->method('getOption')
            ->willReturnMap([
                ['suggested_addresses', null, $suggestedAddresses],
                ['original_address', null, $originalAddress],
            ]);

        $addressValidationResultForm = $this->createMock(FormInterface::class);
        $addressValidationResultForm->expects(self::any())
            ->method('getConfig')
            ->willReturn($formConfig);
        $addressValidationResultForm->expects(self::once())
            ->method('submit')
            ->with(['address' => '0']);
        $addressValidationResultForm->expects(self::never())
            ->method('handleRequest');
        $addressValidationResultForm->expects(self::once())
            ->method('isSubmitted')
            ->willReturn(true);
        $addressValidationResultForm->expects(self::once())
            ->method('isValid')
            ->willReturn(true);
        $addressValidationResultForm->expects(self::once())
            ->method('getData')
            ->willReturn(['address' => $selectedAddress]);
        $this->authorizationChecker->expects(self::once())
            ->method('isGranted')
            ->with(BasicPermission::EDIT, $addressBookAddress)
            ->willReturn(true);

        $this->addressCopier->expects(self::never())
            ->method(self::anything());

        $this->entityManager->expects(self::once())
            ->method('flush');

        self::assertNull($selectedAddress->getValidatedAt());
        self::assertNull($addressBookAddress->getValidatedAt());

        $this->handler->handleAddressValidationRequest($addressValidationResultForm, $request);

        self::assertInstanceOf(\DateTimeInterface::class, $selectedAddress->getValidatedAt());
        self::assertEquals($selectedAddress->getValidatedAt(), $addressBookAddress->getValidatedAt());
    }

    public function testUpdatesAddressBookAddressWhenSuggestedAddressSelectedAndUpdateIsChecked(): void
    {
        $request = new Request();
        $originalAddress = (new OrderAddress())
            ->setLabel('Original Address');
        $addressBookAddress = new CustomerUserAddress();
        AddressBookAddressUtils::setAddressBookAddress($originalAddress, $addressBookAddress);
        $selectedAddress = (new OrderAddress())
            ->setLabel('Selected Address');
        $suggestedAddresses = [$selectedAddress];

        $formConfig = $this->createMock(FormConfigInterface::class);
        $formConfig->expects(self::any())
            ->method('getOption')
            ->willReturnMap([
                ['suggested_addresses', null, $suggestedAddresses],
                ['original_address', null, $originalAddress],
            ]);

        $addressValidationResultForm = $this->createMock(FormInterface::class);
        $addressValidationResultForm->expects(self::any())
            ->method('getConfig')
            ->willReturn($formConfig);
        $addressValidationResultForm->expects(self::never())
            ->method('submit');
        $addressValidationResultForm->expects(self::once())
            ->method('handleRequest')
            ->with($request);
        $addressValidationResultForm->expects(self::once())
            ->method('isSubmitted')
            ->willReturn(true);
        $addressValidationResultForm->expects(self::once())
            ->method('isValid')
            ->willReturn(true);
        $addressValidationResultForm->expects(self::once())
            ->method('getData')
            ->willReturn(['address' => $selectedAddress, 'update_address' => true]);

        $this->authorizationChecker->expects(self::never())
            ->method('isGranted');

        $this->addressCopier->expects(self::once())
            ->method('copyToAddress')
            ->with($selectedAddress, $addressBookAddress);

        $this->entityManager->expects(self::once())
            ->method('flush');

        self::assertNull($selectedAddress->getValidatedAt());

        $this->handler->handleAddressValidationRequest($addressValidationResultForm, $request);

        self::assertInstanceOf(\DateTimeInterface::class, $selectedAddress->getValidatedAt());
    }

    public function testNotUpdatesAddressBookAddressWhenSuggestedAddressSelectedAndUpdateIsNotChecked(): void
    {
        $request = new Request();
        $originalAddress = (new OrderAddress())
            ->setLabel('Original Address');
        $addressBookAddress = new CustomerUserAddress();
        AddressBookAddressUtils::setAddressBookAddress($originalAddress, $addressBookAddress);
        $selectedAddress = (new OrderAddress())
            ->setLabel('Selected Address');
        $suggestedAddresses = [$selectedAddress];

        $formConfig = $this->createMock(FormConfigInterface::class);
        $formConfig->expects(self::any())
            ->method('getOption')
            ->willReturnMap([
                ['suggested_addresses', null, $suggestedAddresses],
                ['original_address', null, $originalAddress],
            ]);

        $addressValidationResultForm = $this->createMock(FormInterface::class);
        $addressValidationResultForm->expects(self::any())
            ->method('getConfig')
            ->willReturn($formConfig);
        $addressValidationResultForm->expects(self::never())
            ->method('submit');
        $addressValidationResultForm->expects(self::once())
            ->method('handleRequest')
            ->with($request);
        $addressValidationResultForm->expects(self::once())
            ->method('isSubmitted')
            ->willReturn(true);
        $addressValidationResultForm->expects(self::once())
            ->method('isValid')
            ->willReturn(true);
        $addressValidationResultForm->expects(self::once())
            ->method('getData')
            ->willReturn(['address' => $selectedAddress, 'update_address' => false]);

        $this->authorizationChecker->expects(self::never())
            ->method('isGranted');

        $this->addressCopier->expects(self::never())
            ->method('copyToAddress');

        $this->entityManager->expects(self::never())
            ->method('flush');

        self::assertNull($selectedAddress->getValidatedAt());

        $this->handler->handleAddressValidationRequest($addressValidationResultForm, $request);

        self::assertInstanceOf(\DateTimeInterface::class, $selectedAddress->getValidatedAt());
    }

    public function testCreatesCustomerUserAddressWhenOriginalAddressSelectedAndCreateIsChecked(): void
    {
        $request = new Request();
        $originalAddress = (new OrderAddress())
            ->setLabel('Original Address');
        $addressBookAddress = new CustomerUserAddress();
        AddressBookAddressUtils::setAddressBookAddress($originalAddress, $addressBookAddress);
        $suggestedAddress = (new OrderAddress())
            ->setLabel('Selected Address');
        $suggestedAddresses = [$suggestedAddress];
        $customer = new Customer();
        $customerUser = new CustomerUser();

        $formConfig = $this->createMock(FormConfigInterface::class);
        $formConfig->expects(self::any())
            ->method('getOption')
            ->willReturnMap([
                ['suggested_addresses', null, $suggestedAddresses],
                ['original_address', null, $originalAddress],
                ['address_book_new_address_class', null, CustomerUserAddress::class],
                ['customer', null, $customer],
                ['customer_user', null, $customerUser],
            ]);

        $addressValidationResultForm = $this->createMock(FormInterface::class);
        $addressValidationResultForm->expects(self::any())
            ->method('getConfig')
            ->willReturn($formConfig);
        $addressValidationResultForm->expects(self::never())
            ->method('submit');
        $addressValidationResultForm->expects(self::once())
            ->method('handleRequest')
            ->with($request);
        $addressValidationResultForm->expects(self::once())
            ->method('isSubmitted')
            ->willReturn(true);
        $addressValidationResultForm->expects(self::once())
            ->method('isValid')
            ->willReturn(true);
        $addressValidationResultForm->expects(self::once())
            ->method('getData')
            ->willReturn(['address' => $originalAddress, 'update_address' => false, 'create_address' => true]);

        $this->authorizationChecker->expects(self::never())
            ->method('isGranted');

        $this->addressCopier->expects(self::once())
            ->method('copyToAddress')
            ->with(
                $originalAddress,
                self::callback(function (CustomerUserAddress $addressBookAddress) use ($customerUser) {
                    self::assertEquals($customerUser, $addressBookAddress->getFrontendOwner());
                    self::assertEquals(
                        new ArrayCollection([$this->shippingAddressType]),
                        $addressBookAddress->getTypes()
                    );

                    return true;
                })
            );

        $this->entityManager->expects(self::once())
            ->method('flush');

        $this->handler->handleAddressValidationRequest($addressValidationResultForm, $request);

        $addressBookAddress = AddressBookAddressUtils::getAddressBookAddress($originalAddress);
        self::assertInstanceOf(CustomerUserAddress::class, $addressBookAddress);
    }

    public function testCreatesCustomerUserAddressWhenSuggestedAddressSelectedAndCreateIsChecked(): void
    {
        $request = new Request();
        $originalAddress = (new OrderAddress())
            ->setLabel('Original Address');
        $addressBookAddress = new CustomerUserAddress();
        AddressBookAddressUtils::setAddressBookAddress($originalAddress, $addressBookAddress);
        $selectedAddress = (new OrderAddress())
            ->setLabel('Selected Address');
        $suggestedAddresses = [$selectedAddress];
        $customer = new Customer();
        $customerUser = new CustomerUser();

        $formConfig = $this->createMock(FormConfigInterface::class);
        $formConfig->expects(self::any())
            ->method('getOption')
            ->willReturnMap([
                ['suggested_addresses', null, $suggestedAddresses],
                ['original_address', null, $originalAddress],
                ['address_book_new_address_class', null, CustomerUserAddress::class],
                ['customer', null, $customer],
                ['customer_user', null, $customerUser],
            ]);

        $addressValidationResultForm = $this->createMock(FormInterface::class);
        $addressValidationResultForm->expects(self::any())
            ->method('getConfig')
            ->willReturn($formConfig);
        $addressValidationResultForm->expects(self::never())
            ->method('submit');
        $addressValidationResultForm->expects(self::once())
            ->method('handleRequest')
            ->with($request);
        $addressValidationResultForm->expects(self::once())
            ->method('isSubmitted')
            ->willReturn(true);
        $addressValidationResultForm->expects(self::once())
            ->method('isValid')
            ->willReturn(true);
        $addressValidationResultForm->expects(self::once())
            ->method('getData')
            ->willReturn(['address' => $selectedAddress, 'update_address' => false, 'create_address' => true]);

        $this->authorizationChecker->expects(self::never())
            ->method('isGranted');

        $this->addressCopier->expects(self::once())
            ->method('copyToAddress')
            ->with(
                $selectedAddress,
                self::callback(function (CustomerUserAddress $addressBookAddress) use ($customerUser) {
                    self::assertEquals($customerUser, $addressBookAddress->getFrontendOwner());
                    self::assertEquals(
                        new ArrayCollection([$this->shippingAddressType]),
                        $addressBookAddress->getTypes()
                    );

                    return true;
                })
            );

        $this->entityManager->expects(self::once())
            ->method('flush');

        self::assertNull($selectedAddress->getValidatedAt());

        $this->handler->handleAddressValidationRequest($addressValidationResultForm, $request);

        self::assertInstanceOf(\DateTimeInterface::class, $selectedAddress->getValidatedAt());

        $addressBookAddress = AddressBookAddressUtils::getAddressBookAddress($selectedAddress);
        self::assertInstanceOf(CustomerUserAddress::class, $addressBookAddress);
    }

    public function testCreatesCustomerAddressWhenSuggestedAddressSelectedAndCreateIsChecked(): void
    {
        $request = new Request();
        $originalAddress = (new OrderAddress())
            ->setLabel('Original Address');
        $selectedAddress = (new OrderAddress())
            ->setLabel('Selected Address');
        $suggestedAddresses = [$selectedAddress];
        $customer = new Customer();

        $formConfig = $this->createMock(FormConfigInterface::class);
        $formConfig->expects(self::any())
            ->method('getOption')
            ->willReturnMap([
                ['suggested_addresses', null, $suggestedAddresses],
                ['original_address', null, $originalAddress],
                ['address_book_new_address_class', null, CustomerAddress::class],
                ['customer', null, $customer],
                ['customer_user', null, null],
            ]);

        $addressValidationResultForm = $this->createMock(FormInterface::class);
        $addressValidationResultForm->expects(self::any())
            ->method('getConfig')
            ->willReturn($formConfig);
        $addressValidationResultForm->expects(self::never())
            ->method('submit');
        $addressValidationResultForm->expects(self::once())
            ->method('handleRequest')
            ->with($request);
        $addressValidationResultForm->expects(self::once())
            ->method('isSubmitted')
            ->willReturn(true);
        $addressValidationResultForm->expects(self::once())
            ->method('isValid')
            ->willReturn(true);
        $addressValidationResultForm->expects(self::once())
            ->method('getData')
            ->willReturn(['address' => $selectedAddress, 'update_address' => false, 'create_address' => true]);

        $this->authorizationChecker->expects(self::never())
            ->method('isGranted');

        $this->addressCopier->expects(self::once())
            ->method('copyToAddress')
            ->with(
                $selectedAddress,
                self::callback(function (CustomerAddress $addressBookAddress) use ($customer) {
                    self::assertEquals($customer, $addressBookAddress->getFrontendOwner());
                    self::assertEquals(
                        new ArrayCollection([$this->shippingAddressType]),
                        $addressBookAddress->getTypes()
                    );

                    return true;
                })
            );

        $this->entityManager->expects(self::once())
            ->method('flush');

        self::assertNull($selectedAddress->getValidatedAt());

        $this->handler->handleAddressValidationRequest($addressValidationResultForm, $request);

        self::assertInstanceOf(\DateTimeInterface::class, $selectedAddress->getValidatedAt());

        $addressBookAddress = AddressBookAddressUtils::getAddressBookAddress($selectedAddress);
        self::assertInstanceOf(CustomerAddress::class, $addressBookAddress);
    }

    public function testNotCreatesAddressBookAddressWhenSuggestedAddressSelectedAndCreateIsCheckedButNoClass(): void
    {
        $request = new Request();
        $originalAddress = (new OrderAddress())
            ->setLabel('Original Address');
        $addressBookAddress = new CustomerUserAddress();
        AddressBookAddressUtils::setAddressBookAddress($originalAddress, $addressBookAddress);
        $selectedAddress = (new OrderAddress())
            ->setLabel('Selected Address');
        $suggestedAddresses = [$selectedAddress];
        $customer = new Customer();
        $customerUser = new CustomerUser();

        $formConfig = $this->createMock(FormConfigInterface::class);
        $formConfig->expects(self::any())
            ->method('getOption')
            ->willReturnMap([
                ['suggested_addresses', null, $suggestedAddresses],
                ['original_address', null, $originalAddress],
                ['address_book_new_address_class', null, null],
                ['customer', null, $customer],
                ['customer_user', null, $customerUser],
            ]);

        $addressValidationResultForm = $this->createMock(FormInterface::class);
        $addressValidationResultForm->expects(self::any())
            ->method('getConfig')
            ->willReturn($formConfig);
        $addressValidationResultForm->expects(self::never())
            ->method('submit');
        $addressValidationResultForm->expects(self::once())
            ->method('handleRequest')
            ->with($request);
        $addressValidationResultForm->expects(self::once())
            ->method('isSubmitted')
            ->willReturn(true);
        $addressValidationResultForm->expects(self::once())
            ->method('isValid')
            ->willReturn(true);
        $addressValidationResultForm->expects(self::once())
            ->method('getData')
            ->willReturn(['address' => $selectedAddress, 'update_address' => false, 'create_address' => true]);

        $this->authorizationChecker->expects(self::never())
            ->method('isGranted');

        $this->addressCopier->expects(self::never())
            ->method('copyToAddress');

        $this->entityManager->expects(self::never())
            ->method('flush');

        self::assertNull($selectedAddress->getValidatedAt());

        $this->handler->handleAddressValidationRequest($addressValidationResultForm, $request);

        self::assertInstanceOf(\DateTimeInterface::class, $selectedAddress->getValidatedAt());
        self::assertNull(AddressBookAddressUtils::getAddressBookAddress($selectedAddress));
    }
}
