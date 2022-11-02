<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Acl\Domain;

use Oro\Bundle\CustomerBundle\Acl\Domain\PermissionGrantingStrategy;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Acl\Domain\DomainObjectWrapper;
use Oro\Bundle\SecurityBundle\Acl\Domain\PermissionGrantingStrategy as InnerStrategy;
use Oro\Bundle\SecurityBundle\Acl\Domain\PermissionGrantingStrategyContextInterface;
use Oro\Bundle\SecurityBundle\Acl\Extension\AclExtensionInterface;
use Oro\Bundle\SecurityBundle\Authentication\Token\OrganizationAwareTokenInterface;
use Oro\Bundle\UserBundle\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class PermissionGrantingStrategyTest extends TestCase
{
    /** @var PermissionGrantingStrategy */
    private $strategy;

    /** @var MockObject */
    private $innerStrategy;

    /** @var MockObject */
    private $securityToken;

    /** @var MockObject */
    private $aclExtension;

    /** @var MockObject */
    private $context;

    protected function setUp(): void
    {
        $this->innerStrategy = $this->createMock(InnerStrategy::class);
        $this->securityToken = $this->createMock(OrganizationAwareTokenInterface::class);
        $this->aclExtension = $this->createMock(AclExtensionInterface::class);
        $this->context = $this->createMock(PermissionGrantingStrategyContextInterface::class);

        $this->innerStrategy->expects($this->any())
            ->method('getContext')
            ->willReturn($this->context);

        $this->context->expects($this->any())
            ->method('getSecurityToken')
            ->willReturn($this->securityToken);
        $this->context->expects($this->any())
            ->method('getAclExtension')
            ->willReturn($this->aclExtension);

        $this->strategy = new PermissionGrantingStrategy($this->innerStrategy);
    }

    public function testIsGrantedWithNotCustomerUserInToken()
    {
        $innerStrategyResult = false;

        $this->securityToken->expects($this->once())
            ->method('getUser')
            ->willReturn(new User());

        $this->innerStrategy->expects($this->once())
            ->method('isGranted')
            ->willReturn($innerStrategyResult);

        $this->assertEquals(
            $innerStrategyResult,
            $this->strategy->isGranted($this->createMock(AclInterface::class), [2, 4], [])
        );
    }

    public function testIsGrantedWithNotSupportedObject()
    {
        $innerStrategyResult = false;

        $this->securityToken->expects($this->once())
            ->method('getUser')
            ->willReturn(new CustomerUser());

        $this->context->expects($this->any())
            ->method('getObject')
            ->willReturn(new CustomerUser());

        $this->innerStrategy->expects($this->once())
            ->method('isGranted')
            ->willReturn($innerStrategyResult);

        $this->assertEquals(
            $innerStrategyResult,
            $this->strategy->isGranted($this->createMock(AclInterface::class), [2, 4], [])
        );
    }

    public function testIsGrantedWithRoleWithCustomer()
    {
        $innerStrategyResult = false;

        $role = new CustomerUserRole('');

        $this->securityToken->expects($this->once())
            ->method('getUser')
            ->willReturn(new CustomerUser());

        $role->setCustomer(new Customer());
        $this->context->expects($this->any())
            ->method('getObject')
            ->willReturn($role);

        $this->innerStrategy->expects($this->once())
            ->method('isGranted')
            ->willReturn($innerStrategyResult);

        $this->assertEquals(
            $innerStrategyResult,
            $this->strategy->isGranted($this->createMock(AclInterface::class), [2, 4], [])
        );
    }

    public function testIsGrantedWithNotSupportedPermission()
    {
        $innerStrategyResult = false;

        $role = new CustomerUserRole('');

        $organization = new Organization();
        $organization->setId(2);

        $this->securityToken->expects($this->once())
            ->method('getUser')
            ->willReturn(new CustomerUser());
        $this->securityToken->expects($this->once())
            ->method('getOrganization')
            ->willReturn($organization);

        $this->context->expects($this->any())
            ->method('getObject')
            ->willReturn($role);

        $this->aclExtension->expects($this->never())
            ->method('getPermissions');

        $this->innerStrategy->expects($this->once())
            ->method('isGranted')
            ->willReturn($innerStrategyResult);

        $this->assertEquals(
            $innerStrategyResult,
            $this->strategy->isGranted($this->createMock(AclInterface::class), [2, 4], [])
        );
    }

    public function testIsGrantedWithNotSelfManagedRole()
    {
        $innerStrategyResult = false;

        $role = new CustomerUserRole('');
        $role->setSelfManaged(false);

        $organization = new Organization();
        $organization->setId(2);

        $this->securityToken->expects($this->once())
            ->method('getUser')
            ->willReturn(new CustomerUser());
        $this->securityToken->expects($this->once())
            ->method('getOrganization')
            ->willReturn($organization);

        $this->context->expects($this->any())
            ->method('getObject')
            ->willReturn($role);

        $this->aclExtension->expects($this->never())
            ->method('getPermissions');

        $this->innerStrategy->expects($this->once())
            ->method('isGranted')
            ->willReturn($innerStrategyResult);

        $this->assertEquals(
            $innerStrategyResult,
            $this->strategy->isGranted($this->createMock(AclInterface::class), [2, 4], [])
        );
    }

    public function testIsGrantedWithNotPublicRole()
    {
        $innerStrategyResult = false;

        $role = new CustomerUserRole('');
        $role->setSelfManaged(true);
        $role->setPublic(false);

        $organization = new Organization();
        $organization->setId(2);

        $this->securityToken->expects($this->once())
            ->method('getUser')
            ->willReturn(new CustomerUser());
        $this->securityToken->expects($this->once())
            ->method('getOrganization')
            ->willReturn($organization);

        $this->context->expects($this->any())
            ->method('getObject')
            ->willReturn($role);

        $this->aclExtension->expects($this->never())
            ->method('getPermissions');

        $this->innerStrategy->expects($this->once())
            ->method('isGranted')
            ->willReturn($innerStrategyResult);

        $this->assertEquals(
            $innerStrategyResult,
            $this->strategy->isGranted($this->createMock(AclInterface::class), [2, 4], [])
        );
    }

    public function testIsGrantedWithRoleFromAnotherOrganization()
    {
        $innerStrategyResult = false;

        $role = new CustomerUserRole('');
        $role->setSelfManaged(true);
        $role->setPublic(true);

        $roleOrganization = new Organization();
        $roleOrganization->setId(3);
        $role->setOrganization($roleOrganization);

        $organization = new Organization();
        $organization->setId(2);

        $this->securityToken->expects($this->once())
            ->method('getUser')
            ->willReturn(new CustomerUser());
        $this->securityToken->expects($this->once())
            ->method('getOrganization')
            ->willReturn($organization);

        $this->context->expects($this->any())
            ->method('getObject')
            ->willReturn($role);

        $this->aclExtension->expects($this->once())
            ->method('getPermissions')
            ->with(2)
            ->willReturn(['VIEW', 'DELETE']);

        $this->innerStrategy->expects($this->once())
            ->method('isGranted')
            ->willReturn($innerStrategyResult);

        $this->assertEquals(
            $innerStrategyResult,
            $this->strategy->isGranted($this->createMock(AclInterface::class), [2, 4], [])
        );
    }

    public function testIsGrantedWhenSecurityTokenDoesNotHaveOrganization()
    {
        $innerStrategy = $this->createMock(InnerStrategy::class);
        $securityToken = $this->createMock(TokenInterface::class);
        $context = $this->createMock(PermissionGrantingStrategyContextInterface::class);
        $innerStrategy->expects($this->atLeastOnce())
            ->method('getContext')
            ->willReturn($context);
        $context->expects($this->once())
            ->method('getSecurityToken')
            ->willReturn($securityToken);
        $context->expects($this->once())
            ->method('getAclExtension')
            ->willReturn($this->aclExtension);
        $strategy = new PermissionGrantingStrategy($innerStrategy);

        $organization = new Organization();
        $organization->setId(2);

        $role = new CustomerUserRole('');
        $role->setSelfManaged(true);
        $role->setPublic(true);
        $role->setOrganization($organization);

        $securityToken->expects($this->once())
            ->method('getUser')
            ->willReturn(new CustomerUser());

        $context->expects($this->any())
            ->method('getObject')
            ->willReturn($role);

        $this->aclExtension->expects($this->once())
            ->method('getPermissions')
            ->with(2)
            ->willReturn(['VIEW', 'DELETE']);

        $innerStrategy->expects($this->never())
            ->method('isGranted');

        $this->assertEquals(
            true,
            $strategy->isGranted($this->createMock(AclInterface::class), [2, 4], [])
        );
    }

    public function testIsGranted()
    {
        $organization = new Organization();
        $organization->setId(2);

        $role = new CustomerUserRole('');
        $role->setSelfManaged(true);
        $role->setPublic(true);
        $role->setOrganization($organization);

        $this->securityToken->expects($this->once())
            ->method('getUser')
            ->willReturn(new CustomerUser());
        $this->securityToken->expects($this->once())
            ->method('getOrganization')
            ->willReturn($organization);

        $this->context->expects($this->any())
            ->method('getObject')
            ->willReturn($role);

        $this->aclExtension->expects($this->once())
            ->method('getPermissions')
            ->with(2)
            ->willReturn(['VIEW', 'DELETE']);

        $this->innerStrategy->expects($this->never())
            ->method('isGranted');

        $this->assertEquals(
            true,
            $this->strategy->isGranted($this->createMock(AclInterface::class), [2, 4], [])
        );
    }

    public function testIsGrantedWithDomainObjectWrapper()
    {
        $organization = new Organization();
        $organization->setId(2);

        $role = new CustomerUserRole('');
        $role->setSelfManaged(true);
        $role->setPublic(true);
        $role->setOrganization($organization);

        $wrapper = new DomainObjectWrapper($role, new ObjectIdentity('test', 'test'));

        $this->securityToken->expects($this->once())
            ->method('getUser')
            ->willReturn(new CustomerUser());
        $this->securityToken->expects($this->once())
            ->method('getOrganization')
            ->willReturn($organization);

        $this->context->expects($this->any())
            ->method('getObject')
            ->willReturn($wrapper);

        $this->aclExtension->expects($this->once())
            ->method('getPermissions')
            ->with(2)
            ->willReturn(['VIEW', 'DELETE']);

        $this->innerStrategy->expects($this->never())
            ->method('isGranted');

        $this->assertEquals(
            true,
            $this->strategy->isGranted($this->createMock(AclInterface::class), [2, 4], [])
        );
    }
}
