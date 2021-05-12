<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity;

use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Entity\GuestCustomerUserManager;
use Oro\Bundle\CustomerBundle\Provider\CustomerUserRelationsProvider;
use Oro\Bundle\CustomerBundle\Tests\Unit\Entity\Stub\WebsiteStub;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Provider\DefaultUserProvider;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class GuestCustomerUserManagerTest extends \PHPUnit\Framework\TestCase
{
    /** @var WebsiteManager|\PHPUnit\Framework\MockObject\MockObject */
    private $websiteManager;

    /** @var CustomerUserManager|\PHPUnit\Framework\MockObject\MockObject */
    private $customerUserManager;

    /** @var CustomerUserRelationsProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $relationsProvider;

    /** @var DefaultUserProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $defaultUserProvider;

    /** @var GuestCustomerUserManager */
    private $guestCustomerUserManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->websiteManager = $this->createMock(WebsiteManager::class);
        $this->customerUserManager = $this->createMock(CustomerUserManager::class);
        $this->relationsProvider = $this->createMock(CustomerUserRelationsProvider::class);
        $this->defaultUserProvider = $this->createMock(DefaultUserProvider::class);

        $this->guestCustomerUserManager = new GuestCustomerUserManager(
            $this->websiteManager,
            $this->customerUserManager,
            $this->relationsProvider,
            $this->defaultUserProvider,
            new PropertyAccessor()
        );
    }

    public function expectsGuestCustomerUserInitialization(
        Website $website,
        User $owner,
        CustomerGroup $customerGroup
    ): array {
        $website->setName('Default Website');
        $website->setOrganization(new Organization());
        $website->setDefaultRole(new CustomerUserRole());

        $owner->setFirstName('owner name');

        $customerGroup->setName('Default Customer Group');
        $customerGroup->setOwner($owner);

        $this->customerUserManager->expects($this->once())
            ->method('generatePassword')
            ->with(10)
            ->willReturn('1234567890');
        $this->customerUserManager->expects($this->once())
            ->method('updatePassword');

        $this->defaultUserProvider->expects($this->once())
            ->method('getDefaultUser')
            ->with('oro_customer', 'default_customer_owner')
            ->willReturn($owner);

        $this->websiteManager->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $this->relationsProvider->expects($this->once())
            ->method('getCustomerGroup')
            ->willReturn($customerGroup);

        return [
            'email'      => 'test@example.com',
            'first_name' => 'First Name',
            'last_name'  => 'Last Name'
        ];
    }

    public function assertGuestCustomerUserInitialization(
        CustomerUser $customerUser,
        array $properties,
        Website $website,
        User $owner,
        CustomerGroup $customerGroup
    ): void {
        $this->assertEquals($properties['email'], $customerUser->getEmail());
        $this->assertEquals($properties['first_name'], $customerUser->getFirstName());
        $this->assertEquals($properties['last_name'], $customerUser->getLastName());
        $this->assertTrue($customerUser->isGuest());
        $this->assertFalse($customerUser->isConfirmed());
        $this->assertFalse($customerUser->isEnabled());
        $this->assertSame($customerGroup, $customerUser->getCustomer()->getGroup());
        $this->assertEquals($owner->getFirstName(), $customerUser->getOwner()->getFirstName());
        $this->assertSame($customerUser->getRoles(), [$website->getDefaultRole()]);
    }

    public function testGenerateGuestCustomerUser(): void
    {
        $website = new WebsiteStub();
        $owner = new User();
        $customerGroup = new CustomerGroup();

        $properties = $this->expectsGuestCustomerUserInitialization($website, $owner, $customerGroup);
        $customerUser = $this->guestCustomerUserManager->generateGuestCustomerUser($properties);
        $this->assertGuestCustomerUserInitialization($customerUser, $properties, $website, $owner, $customerGroup);
    }

    public function testInitializeGuestCustomerUser(): void
    {
        $website = new WebsiteStub();
        $owner = new User();
        $customerGroup = new CustomerGroup();

        $properties = $this->expectsGuestCustomerUserInitialization($website, $owner, $customerGroup);
        $customerUser = new CustomerUser();
        $this->guestCustomerUserManager->initializeGuestCustomerUser($customerUser, $properties);
        $this->assertGuestCustomerUserInitialization($customerUser, $properties, $website, $owner, $customerGroup);
    }
}
