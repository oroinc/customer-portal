<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Security\CustomerUserProvider;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\SecurityBundle\Acl\Extension\AclExtensionSelector;
use Oro\Bundle\SecurityBundle\Acl\Extension\EntityAclExtension;
use Oro\Bundle\SecurityBundle\Acl\Extension\EntityMaskBuilder;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\Security\Acl\Domain\Entry;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Role\Role;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerUserProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var AuthorizationCheckerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $authChecker;

    /** @var TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenAccessor;

    /** @var AclManager|\PHPUnit\Framework\MockObject\MockObject */
    private $aclManager;

    /** @var CustomerUserProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $this->aclManager = $this->createMock(AclManager::class);

        $this->provider = new CustomerUserProvider(
            $this->authChecker,
            $this->tokenAccessor,
            $this->aclManager
        );
    }

    public function testGetLoggedUserIncludingGuest()
    {
        $token = $this->createMock(AnonymousCustomerUserToken::class);
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $this->tokenAccessor->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $visitor = $this->createMock(CustomerVisitor::class);
        $token->expects($this->once())
            ->method('getVisitor')
            ->willReturn($visitor);

        $guestUser = $this->createMock(CustomerUser::class);
        $visitor->expects($this->once())
            ->method('getCustomerUser')
            ->willReturn($guestUser);

        $this->assertSame($guestUser, $this->provider->getLoggedUser(true));
    }

    public function testIsGrantedOidMaskAcesFilteredOrNotPresent()
    {
        $extension = $this->mockExtension();

        $maskBuilder = new EntityMaskBuilder(0, ['EDIT']);
        $extension->expects($this->once())->method('getMaskBuilder')
            ->with('EDIT')
            ->willReturn($maskBuilder);

        $oid = new ObjectIdentity('id', 'class');

        $this->aclManager->expects($this->once())
            ->method('getOid')
            ->with('entity:' . \stdClass::class)
            ->willReturn($oid);
        $user = $this->mockUser();

        /** @var Role|\PHPUnit\Framework\MockObject\MockObject $role */
        $role = $this->createMock(Role::class);

        $user->expects($this->exactly(2))
            ->method('getRoles')
            ->willReturnOnConsecutiveCalls([$role], [/*empty for second call just to avoid unnecessary mocks*/]);

        $sid = $this->mockSid($role);

        $this->aclManager->expects($this->once())
            ->method('getAces')
            ->with($sid, $oid)
            ->willReturn([]);

        /** @var ObjectIdentity|\PHPUnit\Framework\MockObject\MockObject $rootOid */
        $rootOid = new ObjectIdentity('id', '(root)');
        $this->aclManager->expects($this->once())
            ->method('getRootOid')
            ->with($oid)
            ->willReturn($rootOid);

        $extension->expects($this->once())->method('getAllMaskBuilders')
            ->willReturn([$maskBuilder]);

        $extension->expects($this->once())->method('getServiceBits')->with(1)->willReturn(0);

        $this->provider->isGrantedEditBasic(\stdClass::class);
    }

    public function testIsGrantedOidMaskAcesFilteredByServiceBits()
    {
        $extension = $this->mockExtension();

        $maskBuilder = new EntityMaskBuilder(0, ['EDIT']);
        $extension->expects($this->at(0))->method('getMaskBuilder')
            ->with('EDIT')
            ->willReturn($maskBuilder);

        $oid = new ObjectIdentity('id', 'class');

        $this->aclManager->expects($this->once())
            ->method('getOid')
            ->with('entity:' . \stdClass::class)
            ->willReturn($oid);
        $user = $this->mockUser();

        /** @var Role|\PHPUnit\Framework\MockObject\MockObject $role */
        $role = $this->createMock(Role::class);

        $user->expects($this->exactly(2))
            ->method('getRoles')
            ->willReturnOnConsecutiveCalls([$role], [/*empty for second call just to avoid unnecessary mocks*/]);

        $sid = $this->mockSid($role);

        $ace = $this->createMock(Entry::class);

        $this->aclManager->expects($this->once())
            ->method('getAces')
            ->with($sid, $oid)
            ->willReturn([$ace]);

        $ace->expects($this->once())->method('getMask')->willReturn(256);

        //service bits of requiredMask don`t match serviceBits from aceMask
        $extension->expects($this->at(1))->method('getServiceBits')->with(1)->willReturn(1);
        $extension->expects($this->at(2))->method('getServiceBits')->with(256)->willReturn(0);

        /** @var ObjectIdentity|\PHPUnit\Framework\MockObject\MockObject $rootOid */
        $rootOid = new ObjectIdentity('id', '(root)');
        $this->aclManager->expects($this->once())
            ->method('getRootOid')
            ->with($oid)
            ->willReturn($rootOid);

        $extension->expects($this->once())->method('getAllMaskBuilders')
            ->willReturn([$maskBuilder]);

        $extension->expects($this->at(3))->method('getServiceBits')->with(1)->willReturn(0);

        $this->provider->isGrantedEditBasic(\stdClass::class);
    }

    public function testIsGrantedOidMaskAcesFilteredByIdentityIdentifier()
    {
        $extension = $this->mockExtension();

        $maskBuilder = new EntityMaskBuilder(0, ['EDIT']);
        $extension->expects($this->at(0))->method('getMaskBuilder')
            ->with('EDIT')
            ->willReturn($maskBuilder);

        $oid = new ObjectIdentity('id', 'class');

        $this->aclManager->expects($this->once())
            ->method('getOid')
            ->with('entity:' . \stdClass::class)
            ->willReturn($oid);
        $user = $this->mockUser();

        /** @var Role|\PHPUnit\Framework\MockObject\MockObject $role */
        $role = $this->createMock(Role::class);

        $user->expects($this->exactly(2))
            ->method('getRoles')
            ->willReturnOnConsecutiveCalls([$role], [/*empty for second call just to avoid unnecessary mocks*/]);

        $sid = $this->mockSid($role);

        $ace = $this->createMock(Entry::class);

        $this->aclManager->expects($this->once())
            ->method('getAces')
            ->with($sid, $oid)
            ->willReturn([$ace]);

        $ace->expects($this->once())->method('getMask')->willReturn(256);

        $extension->expects($this->at(1))
            ->method('getServiceBits')->with(1)->willReturn(1);
        $extension->expects($this->at(2))
            ->method('getServiceBits')->with(256)->willReturn(1);

        //different identifiers - so filtered
        $acl = $this->mockAclWithIdentityIdentifier('identifierA');
        $extension->expects($this->at(3))
            ->method('getExtensionKey')->willReturn('identifierB');

        $ace->expects($this->once())->method('getAcl')->willReturn($acl);

        /** @var ObjectIdentity|\PHPUnit\Framework\MockObject\MockObject $rootOid */
        $rootOid = new ObjectIdentity('id', '(root)');
        $this->aclManager->expects($this->once())
            ->method('getRootOid')
            ->with($oid)
            ->willReturn($rootOid);

        $extension->expects($this->once())->method('getAllMaskBuilders')
            ->willReturn([$maskBuilder]);

        $extension->expects($this->at(4))->method('getServiceBits')->with(1)->willReturn(0);

        $this->provider->isGrantedEditBasic(\stdClass::class);
    }

    public function testIsGrantedOidMaskByEqualityAcesNotFiltered()
    {
        $extension = $this->mockExtension();

        $maskBuilder = new EntityMaskBuilder(0, ['EDIT']);
        $extension->expects($this->at(0))->method('getMaskBuilder')
            ->with('EDIT')
            ->willReturn($maskBuilder);

        $oid = new ObjectIdentity('id', 'class');

        $this->aclManager->expects($this->once())
            ->method('getOid')
            ->with('entity:' . \stdClass::class)
            ->willReturn($oid);
        $user = $this->mockUser();

        /** @var Role|\PHPUnit\Framework\MockObject\MockObject $role */
        $role = $this->createMock(Role::class);

        $user->expects($this->once())
            ->method('getRoles')
            ->willReturnOnConsecutiveCalls([$role]);

        $sid = $this->mockSid($role);

        $ace = $this->createMock(Entry::class);

        $this->aclManager->expects($this->once())
            ->method('getAces')
            ->with($sid, $oid)
            ->willReturn([$ace]);

        $ace->expects($this->exactly(2))->method('getMask')->willReturn(256);

        //bits same
        $extension->expects($this->at(1))
            ->method('getServiceBits')->with(1)->willReturn(1);
        $extension->expects($this->at(2))
            ->method('getServiceBits')->with(256)->willReturn(1);

        //identifiers same
        $acl = $this->mockAclWithIdentityIdentifier('identifierA');
        $extension->expects($this->at(3))
            ->method('getExtensionKey')->willReturn('identifierA');

        //going to match
        $ace->expects($this->once())->method('getAcl')->willReturn($acl);
        $ace->expects($this->once())->method('getStrategy')->willReturn('equal');

        $extension->expects($this->at(4))->method('removeServiceBits')->with(1)->willReturn(256);
        $extension->expects($this->at(5))->method('removeServiceBits')->with(256)->willReturn(256);

        $this->assertTrue($this->provider->isGrantedEditBasic(\stdClass::class));
    }

    /**
     * @param string $identifier
     * @return AclInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function mockAclWithIdentityIdentifier($identifier)
    {
        $acl = $this->createMock(AclInterface::class);
        $identity = new ObjectIdentity($identifier, 'any');
        $acl->expects($this->once())->method('getObjectIdentity')->willReturn($identity);

        return $acl;
    }

    /**
     * @return EntityAclExtension|\PHPUnit\Framework\MockObject\MockObject
     */
    private function mockExtension()
    {
        $extensionSelector = $this->createMock(AclExtensionSelector::class);

        /** @var EntityAclExtension|\PHPUnit\Framework\MockObject\MockObject $extension */
        $extension = $this->createMock(EntityAclExtension::class);

        $extensionSelector->expects($this->any())
            ->method('select')
            ->willReturn($extension);

        $this->aclManager->expects($this->any())
            ->method('getExtensionSelector')
            ->willReturn($extensionSelector);

        return $extension;
    }

    /**
     * @return CustomerUser|\PHPUnit\Framework\MockObject\MockObject
     */
    private function mockUser()
    {
        $user = $this->createMock(CustomerUser::class);

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        $this->tokenAccessor->expects($this->any())
            ->method('getToken')
            ->willReturn($token);

        return $user;
    }

    /**
     * @param Role $role
     * @return SecurityIdentityInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function mockSid(Role $role)
    {
        $sid = $this->createMock(SecurityIdentityInterface::class);

        $this->aclManager->expects($this->once())
            ->method('getSid')
            ->with($role)
            ->willReturn($sid);

        return $sid;
    }
}
