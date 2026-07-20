<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\EventListener\RedirectCustomerAddressSearchToCustomerListener;
use Oro\Bundle\SearchBundle\Event\PrepareResultItemEvent;
use Oro\Bundle\SearchBundle\Query\Result\Item;
use Oro\Component\Testing\ReflectionUtil;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class RedirectCustomerAddressSearchToCustomerListenerTest extends TestCase
{
    private UrlGeneratorInterface&MockObject $urlGenerator;
    private ManagerRegistry&MockObject $doctrine;
    private RedirectCustomerAddressSearchToCustomerListener $listener;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->doctrine = $this->createMock(ManagerRegistry::class);

        $this->listener = new RedirectCustomerAddressSearchToCustomerListener($this->urlGenerator, $this->doctrine);
    }

    public function testProcessSkipsNotEntity(): void
    {
        $this->urlGenerator->expects(self::never())
            ->method('generate');

        $this->listener->process(new PrepareResultItemEvent(new Item()));
    }

    public function testProcessSkipsOtherEntities(): void
    {
        $this->urlGenerator->expects(self::never())
            ->method('generate');

        $this->listener->process(new PrepareResultItemEvent(new Item(Customer::class)));
    }

    public function testProcessSkipsWhenNoFrontendOwner(): void
    {
        $this->urlGenerator->expects(self::never())
            ->method('generate');

        $this->listener->process(
            new PrepareResultItemEvent(
                new Item(CustomerAddress::class),
                new CustomerAddress()
            )
        );
    }

    public function testProcessSkipsWhenAddressNotFound(): void
    {
        $item = new Item(CustomerAddress::class, 42);

        $repository = $this->createMock(ObjectRepository::class);
        $repository->expects(self::once())
            ->method('find')
            ->with(42)
            ->willReturn(null);

        $this->doctrine->expects(self::once())
            ->method('getRepository')
            ->with(CustomerAddress::class)
            ->willReturn($repository);

        $this->urlGenerator->expects(self::never())
            ->method('generate');

        $this->listener->process(new PrepareResultItemEvent($item));

        self::assertNull($item->getRecordUrl());
    }

    public function testProcessSetsCustomerViewUrl(): void
    {
        $customer = new Customer();
        ReflectionUtil::setId($customer, 7);

        $address = new CustomerAddress();
        $address->setFrontendOwner($customer);

        $item = new Item(CustomerAddress::class);

        $this->urlGenerator->expects(self::once())
            ->method('generate')
            ->with('oro_customer_customer_view', ['id' => 7], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('/customer/view/7');

        $this->listener->process(new PrepareResultItemEvent($item, $address));

        self::assertSame('/customer/view/7', $item->getRecordUrl());
    }

    public function testProcessLoadsAddressFromRepositoryWhenEventHasNoEntity(): void
    {
        $customer = new Customer();
        ReflectionUtil::setId($customer, 7);

        $address = new CustomerAddress();
        $address->setFrontendOwner($customer);

        $item = new Item(CustomerAddress::class, 42);

        $repository = $this->createMock(ObjectRepository::class);
        $repository->expects(self::once())
            ->method('find')
            ->with(42)
            ->willReturn($address);

        $this->doctrine->expects(self::once())
            ->method('getRepository')
            ->with(CustomerAddress::class)
            ->willReturn($repository);

        $this->urlGenerator->expects(self::once())
            ->method('generate')
            ->with('oro_customer_customer_view', ['id' => 7], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('/customer/view/7');

        $this->listener->process(new PrepareResultItemEvent($item));

        self::assertSame('/customer/view/7', $item->getRecordUrl());
    }
}
