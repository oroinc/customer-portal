<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Entity\GuestCustomerUserManager;
use Oro\Bundle\CustomerBundle\Provider\CustomerUserRelationsProvider;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\OrderBundle\Entity\OrderAddress;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;

class GuestCustomerUserManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DoctrineHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $doctrineHelper;

    /**
     * @var WebsiteManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $websiteManager;

    /**
     * @var CustomerUserManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerUserManager;

    /**
     * @var CustomerUserRelationsProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerUserRelationsProvider;

    /**
     * @var TokenStorageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tokenStorage;

    /**
     * @var GuestCustomerUserManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $manager;

    protected function setUp()
    {
        $this->doctrineHelper = $this->getMockBuilder(DoctrineHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->websiteManager = $this->createMock(WebsiteManager::class);
        $this->customerUserManager = $this->createMock(CustomerUserManager::class);
        $this->customerUserRelationsProvider = $this->createMock(CustomerUserRelationsProvider::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);

        $this->manager = new GuestCustomerUserManager(
            $this->doctrineHelper,
            $this->websiteManager,
            $this->customerUserManager,
            $this->customerUserRelationsProvider,
            $this->tokenStorage
        );
    }

    public function testCreateFromAddressWithoutUsernameAddressToken()
    {
        $this->websiteManager
            ->expects($this->once())
            ->method('getCurrentWebsite')
            ->will($this->returnValue(new Website()));
        $userName = 'foo';
        $this->customerUserManager
            ->expects($this->exactly(2))
            ->method('generatePassword')
            ->with(10)
            ->will($this->returnValue($userName));

        $customerUser = $this->manager->createFromAddress();
        $this->assertEquals($userName, $customerUser->getEmail());
        $this->assertEquals($userName, $customerUser->getUsername());
        $this->assertTrue($customerUser->isGuest());
        $this->assertNull($customerUser->getFirstName());
    }

    public function testCreateFromAddressWithUsernameAddressToken()
    {
        $this->websiteManager
            ->expects($this->once())
            ->method('getCurrentWebsite')
            ->will($this->returnValue(new Website()));
        $this->customerUserManager
            ->expects($this->once())
            ->method('generatePassword')
            ->with(10)
            ->will($this->returnValue('foo'));

        $userName = 'foo@bar.com';
        $firstName = 'firstName';
        $address = new OrderAddress();
        $address->setFirstName($firstName);
        $customerUser = $this->manager->createFromAddress($userName, $address);
        $this->assertEquals($userName, $customerUser->getEmail());
        $this->assertEquals($userName, $customerUser->getUsername());
        $this->assertTrue($customerUser->isGuest());
        $this->assertEquals($firstName, $customerUser->getFirstName());
    }

    public function testUpdateFromAddress()
    {
        $customerUser = new CustomerUser();
        $customerUser->createCustomer();

        $namePrefix = 'namePrefix';
        $firstName  = 'firstName';
        $middleName = 'middleName';
        $lastName   = 'lastName';
        $nameSuffix = 'nameSuffix';
        $address    = new OrderAddress();

        $address->setNamePrefix($namePrefix);
        $address->setFirstName($firstName);
        $address->setMiddleName($middleName);
        $address->setLastName($lastName);
        $address->setNameSuffix($nameSuffix);

        $updatedCustomerUser = $this->manager->updateFromAddress($customerUser, 'foo@bar.com', $address);

        $this->assertEquals($namePrefix, $updatedCustomerUser->getNamePrefix());
        $this->assertEquals($firstName, $updatedCustomerUser->getFirstName());
        $this->assertEquals($middleName, $updatedCustomerUser->getMiddleName());
        $this->assertEquals($lastName, $updatedCustomerUser->getLastName());
        $this->assertEquals($nameSuffix, $updatedCustomerUser->getNameSuffix());
    }
}
