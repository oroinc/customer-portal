<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Provider;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerAddressRepository;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserAddressRepository;
use Oro\Bundle\CustomerBundle\Provider\FrontendAddressProvider;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FrontendAddressProviderTest extends TestCase
{
    private ManagerRegistry&MockObject $registry;
    private AclHelper&MockObject $aclHelper;

    private string $customerAddressClass = 'customerAddressClass';

    private string $customerUserAddressClass = 'customerUserAddressClass';

    private FrontendAddressProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->aclHelper = $this->createMock(AclHelper::class);

        $this->provider = new FrontendAddressProvider(
            $this->registry,
            $this->aclHelper,
            $this->customerAddressClass,
            $this->customerUserAddressClass
        );
    }

    public function testGetCurrentCustomerAddresses(): void
    {
        $addresses = $this->prepareCustomerAddresses();

        $this->assertSame($addresses, $this->provider->getCurrentCustomerAddresses());

        // test caching
        $this->assertSame($addresses, $this->provider->getCurrentCustomerAddresses());
    }

    public function testGetCurrentCustomerUserAddresses(): void
    {
        $addresses = $this->prepareCustomerUserAddresses();

        $this->assertSame($addresses, $this->provider->getCurrentCustomerUserAddresses());

        // test caching
        $this->assertSame($addresses, $this->provider->getCurrentCustomerUserAddresses());
    }

    /**
     * @return CustomerAddress[]
     */
    private function prepareCustomerAddresses(): array
    {
        $address1 = new CustomerAddress();
        $address2 = new CustomerAddress();
        $addresses = [
            $address1,
            $address2,
        ];

        $repository = $this->createMock(CustomerAddressRepository::class);
        $repository->expects($this->once())
            ->method('getAddresses')
            ->with($this->aclHelper)
            ->willReturn($addresses);

        $manager = $this->createMock(EntityManagerInterface::class);
        $manager->expects($this->any())
            ->method('getRepository')
            ->with($this->customerAddressClass)
            ->willReturn($repository);

        $this->registry->expects($this->any())
            ->method('getManagerForClass')
            ->with($this->customerAddressClass)
            ->willReturn($manager);

        return $addresses;
    }

    /**
     * @return CustomerUserAddress[]
     */
    private function prepareCustomerUserAddresses(): array
    {
        $address1 = new CustomerUserAddress();
        $address2 = new CustomerUserAddress();
        $addresses = [
            $address1,
            $address2,
        ];

        $repository = $this->createMock(CustomerUserAddressRepository::class);
        $repository->expects($this->once())
            ->method('getAddresses')
            ->with($this->aclHelper)
            ->willReturn($addresses);

        $manager = $this->createMock(EntityManagerInterface::class);
        $manager->expects($this->any())
            ->method('getRepository')
            ->with($this->customerUserAddressClass)
            ->willReturn($repository);

        $this->registry->expects($this->any())
            ->method('getManagerForClass')
            ->with($this->customerUserAddressClass)
            ->willReturn($manager);

        return $addresses;
    }
}
