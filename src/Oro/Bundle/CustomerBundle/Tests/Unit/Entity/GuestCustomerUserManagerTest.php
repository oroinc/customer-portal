<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Entity\GuestCustomerUserManager;
use Oro\Bundle\CustomerBundle\Provider\CustomerUserRelationsProvider;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
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
     * @var GuestCustomerUserManager
     */
    protected $manager;

    protected function setUp()
    {
        $this->doctrineHelper                = $this->createMock(DoctrineHelper::class);
        $this->websiteManager                = $this->createMock(WebsiteManager::class);
        $this->customerUserManager           = $this->createMock(CustomerUserManager::class);
        $this->customerUserRelationsProvider = $this->createMock(CustomerUserRelationsProvider::class);

        $this->manager = new GuestCustomerUserManager(
            $this->doctrineHelper,
            $this->websiteManager,
            $this->customerUserManager,
            $this->customerUserRelationsProvider
        );
    }

    public function testGetOrCreateByVisitorWithCustomerUser()
    {
        $visitor = new CustomerVisitor();
        $guestCustomerUser = new CustomerUser();
        $visitor->setCustomerUser($guestCustomerUser);
        $this->assertEquals($guestCustomerUser, $this->manager->getOrCreate($visitor));
    }

    public function testGetOrCreateByVisitorWithoutCustomerUser()
    {
        $website = new Website();
        $organization = new Organization();
        $website->setOrganization($organization);
        $this->websiteManager->expects($this->once())
            ->method('getCurrentWebsite')
            ->will($this->returnValue($website));

        $customerVisitorEntityManager = $this->createMock(EntityManager::class);
        $customerVisitorEntityManager->expects($this->once())
            ->method('flush');
        $customerUserEntityManager = $this->createMock(EntityManager::class);
        $customerUserEntityManager->expects($this->once())
            ->method('flush');
        $this->doctrineHelper->expects($this->exactly(2))
            ->method('getEntityManagerForClass')
            ->will($this->returnValueMap([
                 [CustomerVisitor::class, true, $customerVisitorEntityManager],
                 [CustomerUser::class, true, $customerUserEntityManager]
             ]));

        $guestCustomerUser = $this->manager->getOrCreate(new CustomerVisitor());
        $this->assertInstanceOf(CustomerUser::class, $guestCustomerUser);
        $this->assertTrue($guestCustomerUser->isGuest());
    }
}
