<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\EventListener;

use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Form\EventListener\FixCustomerAddressesDefaultSubscriber;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class FixCustomerAddressesDefaultSubscriberTest extends \PHPUnit\Framework\TestCase
{
    /** @var FixCustomerAddressesDefaultSubscriber */
    private $subscriber;

    protected function setUp(): void
    {
        $this->subscriber = new FixCustomerAddressesDefaultSubscriber('frontendOwner.addresses');
    }

    public function testGetSubscribedEvents()
    {
        $this->assertEquals(
            [FormEvents::POST_SUBMIT => 'postSubmit'],
            $this->subscriber->getSubscribedEvents()
        );
    }

    /**
     * @dataProvider postSubmitDataProvider
     */
    public function testPostSubmit(array $allAddresses, string $formAddressKey, array $expectedAddressesData)
    {
        // Set owner for all addresses
        $customer = new Customer();
        foreach ($allAddresses as $address) {
            $customer->addAddress($address);
        }

        $event = $this->createMock(FormEvent::class);
        $event->expects($this->once())
            ->method('getData')
            ->willReturn($allAddresses[$formAddressKey]);

        $this->subscriber->postSubmit($event);

        foreach ($expectedAddressesData as $addressKey => $expectedData) {
            /** @var CustomerAddress $address */
            $address = $allAddresses[$addressKey];

            $defaultTypeNames = [];
            /** @var AddressType $defaultType */
            foreach ($address->getDefaults() as $defaultType) {
                $defaultTypeNames[] = $defaultType->getName();
            }
            $this->assertEquals($expectedData['defaults'], $defaultTypeNames);
        }
    }

    public function postSubmitDataProvider(): array
    {
        $billing = new AddressType(AddressType::TYPE_BILLING);
        $shipping = new AddressType(AddressType::TYPE_SHIPPING);

        return [
            'default' => [
                'allAddresses' => [
                    'foo' => (new CustomerAddress())->addType($billing)->setDefaults([$billing]),
                    'bar' => (new CustomerAddress())->addType($billing)->setDefaults([$billing]),
                    'baz' => (new CustomerAddress())->addType($billing)->addType($shipping)->setDefaults([
                            $billing,
                            $shipping
                        ]),
                ],
                'formAddressKey' => 'foo',
                'expectedAddressesData' => [
                    'foo' => ['defaults' => [AddressType::TYPE_BILLING]],
                    'bar' => ['defaults' => []],
                    'baz' => ['defaults' => [AddressType::TYPE_SHIPPING]],
                ]
            ],
            'change_default_after_remove' => [
                'allAddresses' => [
                    'foo' => (new CustomerAddress())->addType($billing)->setDefaults([$billing])->removeType($billing),
                ],
                'formAddressKey' => 'foo',
                'expectedAddressesData' => [
                    'foo' => ['defaults' => []],
                ]
            ],
        ];
    }
}
