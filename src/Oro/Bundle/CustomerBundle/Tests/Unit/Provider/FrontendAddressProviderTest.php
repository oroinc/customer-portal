<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerAddressRepository;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserAddressRepository;
use Oro\Bundle\CustomerBundle\Provider\FrontendAddressProvider;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class FrontendAddressProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var FrontendAddressProvider */
    private $provider;

    /** @var ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject */
    private $registry;

    /** @var AclHelper|\PHPUnit_Framework_MockObject_MockObject */
    private $aclHelper;

    /** @var string */
    private $customerAddressClass = 'customerAddressClass';

    /** @var string */
    private $customerUserAddressClass = 'customerUserAddressClass';

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->registry = $this->createMock(ManagerRegistry::class);

        $this->aclHelper = $this->getMockBuilder(AclHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider = new FrontendAddressProvider(
            $this->registry,
            $this->aclHelper,
            $this->customerAddressClass,
            $this->customerUserAddressClass
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        unset($this->provider);
    }

    public function testGetCurrentCustomerAddresses()
    {
        $addresses = $this->prepareCustomerAddresses();

        $this->assertSame($addresses, $this->provider->getCurrentCustomerAddresses());

        // test caching
        $this->assertSame($addresses, $this->provider->getCurrentCustomerAddresses());
    }

    public function testGetCurrentCustomerUserAddresses()
    {
        $addresses = $this->prepareCustomerUserAddresses();

        $this->assertSame($addresses, $this->provider->getCurrentCustomerUserAddresses());

        // test caching
        $this->assertSame($addresses, $this->provider->getCurrentCustomerUserAddresses());
    }

    public function testIsCurrentCustomerAddressesContain()
    {
        $addresses = $this->prepareCustomerAddresses();

        $this->assertTrue($this->provider->isCurrentCustomerAddressesContain($addresses[0]));
    }

    public function testIsCurrentCustomerAddressesContainFalse()
    {
        $this->prepareCustomerAddresses();

        $this->assertFalse($this->provider->isCurrentCustomerAddressesContain(new CustomerAddress()));
    }

    public function testIsCurrentCustomerUserAddressesContain()
    {
        $addresses = $this->prepareCustomerUserAddresses();

        $this->assertTrue($this->provider->isCurrentCustomerUserAddressesContain($addresses[0]));
    }

    public function testIsCurrentCustomerUserAddressesContainFalse()
    {
        $this->prepareCustomerUserAddresses();

        $this->assertFalse($this->provider->isCurrentCustomerUserAddressesContain(new CustomerUserAddress()));
    }

    /**
     * @return CustomerAddress[]
     */
    protected function prepareCustomerAddresses()
    {
        $address1 = new CustomerAddress();
        $address2 = new CustomerAddress();
        $addresses = [
            $address1,
            $address2,
        ];

        $repository = $this->prepareCustomerAddressRepository();
        $repository->expects($this->once())
            ->method('getAddresses')
            ->with($this->aclHelper)
            ->willReturn($addresses);

        return $addresses;
    }

    /**
     * @return CustomerUserAddress[]
     */
    protected function prepareCustomerUserAddresses()
    {
        $address1 = new CustomerUserAddress();
        $address2 = new CustomerUserAddress();
        $addresses = [
            $address1,
            $address2,
        ];

        $repository = $this->prepareCustomerUserAddressRepository();
        $repository->expects($this->once())
            ->method('getAddresses')
            ->with($this->aclHelper)
            ->willReturn($addresses);

        return $addresses;
    }

    /**
     * @return CustomerAddressRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function prepareCustomerAddressRepository()
    {
        $repository = $this->getMockBuilder(CustomerAddressRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager = $this->createMock(EntityManagerInterface::class);
        $manager->expects($this->any())
            ->method('getRepository')
            ->with($this->customerAddressClass)
            ->will($this->returnValue($repository));

        $this->registry->expects($this->any())
            ->method('getManagerForClass')
            ->with($this->customerAddressClass)
            ->will($this->returnValue($manager));

        return $repository;
    }

    /**
     * @return CustomerUserAddressRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function prepareCustomerUserAddressRepository()
    {
        $repository = $this->getMockBuilder(CustomerUserAddressRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager = $this->createMock(EntityManagerInterface::class);
        $manager->expects($this->any())
            ->method('getRepository')
            ->with($this->customerUserAddressClass)
            ->will($this->returnValue($repository));

        $this->registry->expects($this->any())
            ->method('getManagerForClass')
            ->with($this->customerUserAddressClass)
            ->will($this->returnValue($manager));

        return $repository;
    }
}
