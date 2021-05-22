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
        $extension = $this->expectsSelectAclExtension();

        $maskBuilder = new EntityMaskBuilder(0, ['EDIT']);
        $extension->expects($this->once())
            ->method('getMaskBuilder')
            ->with('EDIT')
            ->willReturn($maskBuilder);

        $oid = new ObjectIdentity('id', 'class');

        $this->aclManager->expects($this->once())
            ->method('getOid')
            ->with('entity:' . \stdClass::class)
            ->willReturn($oid);

        $user = $this->expectsGetUser();

        $role = $this->createMock(Role::class);
        $user->expects($this->exactly(2))
            ->method('getRoles')
            ->willReturnOnConsecutiveCalls([$role], [/*empty for second call just to avoid unnecessary mocks*/]);

        $sid = $this->expectsGetSid($role);

        $this->aclManager->expects($this->once())
            ->method('getAces')
            ->with($sid, $oid)
            ->willReturn([]);

        $rootOid = new ObjectIdentity('id', '(root)');
        $this->aclManager->expects($this->once())
            ->method('getRootOid')
            ->with($oid)
            ->willReturn($rootOid);

        $extension->expects($this->once())
            ->method('getAllMaskBuilders')
            ->willReturn([$maskBuilder]);

        $extension->expects($this->once())
            ->method('getServiceBits')
            ->with(1)
            ->willReturn(0);

        $this->provider->isGrantedEditBasic(\stdClass::class);
    }

    public function testIsGrantedOidMaskAcesFilteredByServiceBits()
    {
        $extension = $this->expectsSelectAclExtension();

        $maskBuilder = new EntityMaskBuilder(0, ['EDIT']);
        $extension->expects($this->once())
            ->method('getMaskBuilder')
            ->with('EDIT')
            ->willReturn($maskBuilder);

        $oid = new ObjectIdentity('id', 'class');

        $this->aclManager->expects($this->once())
            ->method('getOid')
            ->with('entity:' . \stdClass::class)
            ->willReturn($oid);

        $user = $this->expectsGetUser();

        $role = $this->createMock(Role::class);

        $user->expects($this->exactly(2))
            ->method('getRoles')
            ->willReturnOnConsecutiveCalls([$role], [/*empty for second call just to avoid unnecessary mocks*/]);

        $sid = $this->expectsGetSid($role);

        $ace = $this->createMock(Entry::class);

        $this->aclManager->expects($this->once())
            ->method('getAces')
            ->with($sid, $oid)
            ->willReturn([$ace]);

        $ace->expects($this->once())
            ->method('getMask')
            ->willReturn(256);

        //service bits of requiredMask don`t match serviceBits from aceMask
        $extension->expects($this->exactly(3))
            ->method('getServiceBits')
            ->withConsecutive([1], [256], [1])
            ->willReturnOnConsecutiveCalls(1, 0, 0);

        $rootOid = new ObjectIdentity('id', '(root)');
        $this->aclManager->expects($this->once())
            ->method('getRootOid')
            ->with($oid)
            ->willReturn($rootOid);

        $extension->expects($this->once())
            ->method('getAllMaskBuilders')
            ->willReturn([$maskBuilder]);

        $this->provider->isGrantedEditBasic(\stdClass::class);
    }

    public function testIsGrantedOidMaskAcesFilteredByIdentityIdentifier()
    {
        $extension = $this->expectsSelectAclExtension();

        $maskBuilder = new EntityMaskBuilder(0, ['EDIT']);
        $extension->expects($this->once())
            ->method('getMaskBuilder')
            ->with('EDIT')
            ->willReturn($maskBuilder);

        $oid = new ObjectIdentity('id', 'class');

        $this->aclManager->expects($this->once())
            ->method('getOid')
            ->with('entity:' . \stdClass::class)
            ->willReturn($oid);

        $user = $this->expectsGetUser();

        $role = $this->createMock(Role::class);

        $user->expects($this->exactly(2))
            ->method('getRoles')
            ->willReturnOnConsecutiveCalls([$role], [/*empty for second call just to avoid unnecessary mocks*/]);

        $sid = $this->expectsGetSid($role);

        $ace = $this->createMock(Entry::class);

        $this->aclManager->expects($this->once())
            ->method('getAces')
            ->with($sid, $oid)
            ->willReturn([$ace]);

        $ace->expects($this->once())
            ->method('getMask')
            ->willReturn(256);

        $extension->expects($this->exactly(3))
            ->method('getServiceBits')
            ->withConsecutive([1], [256], [1])
            ->willReturnOnConsecutiveCalls(1, 1, 0);

        //different identifiers - so filtered
        $acl = $this->getAcl('identifierA');
        $extension->expects($this->once())
            ->method('getExtensionKey')
            ->willReturn('identifierB');

        $ace->expects($this->once())
            ->method('getAcl')
            ->willReturn($acl);

        $rootOid = new ObjectIdentity('id', '(root)');
        $this->aclManager->expects($this->once())
            ->method('getRootOid')
            ->with($oid)
            ->willReturn($rootOid);

        $extension->expects($this->once())
            ->method('getAllMaskBuilders')
            ->willReturn([$maskBuilder]);

        $this->provider->isGrantedEditBasic(\stdClass::class);
    }

    public function testIsGrantedOidMaskByEqualityAcesNotFiltered()
    {
        $extension = $this->expectsSelectAclExtension();

        $maskBuilder = new EntityMaskBuilder(0, ['EDIT']);
        $extension->expects($this->once())
            ->method('getMaskBuilder')
            ->with('EDIT')
            ->willReturn($maskBuilder);

        $oid = new ObjectIdentity('id', 'class');

        $this->aclManager->expects($this->once())
            ->method('getOid')
            ->with('entity:' . \stdClass::class)
            ->willReturn($oid);

        $user = $this->expectsGetUser();

        $role = $this->createMock(Role::class);

        $user->expects($this->once())
            ->method('getRoles')
            ->willReturnOnConsecutiveCalls([$role]);

        $sid = $this->expectsGetSid($role);

        $ace = $this->createMock(Entry::class);

        $this->aclManager->expects($this->once())
            ->method('getAces')
            ->with($sid, $oid)
            ->willReturn([$ace]);

        $ace->expects($this->exactly(2))
            ->method('getMask')
            ->willReturn(256);

        //bits same
        $extension->expects($this->exactly(2))
            ->method('getServiceBits')
            ->withConsecutive([1], [256])
            ->willReturnOnConsecutiveCalls(1, 1);

        //identifiers same
        $acl = $this->getAcl('identifierA');
        $extension->expects($this->once())
            ->method('getExtensionKey')
            ->willReturn('identifierA');

        //going to match
        $ace->expects($this->once())
            ->method('getAcl')
            ->willReturn($acl);
        $ace->expects($this->once())
            ->method('getStrategy')
            ->willReturn('equal');

        $extension->expects($this->exactly(2))
            ->method('removeServiceBits')
            ->withConsecutive([1], [256])
            ->willReturnOnConsecutiveCalls(256, 256);

        $this->assertTrue($this->provider->isGrantedEditBasic(\stdClass::class));
    }

    /**
     * @return AclInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getAcl(string $identifier)
    {
        $acl = $this->createMock(AclInterface::class);
        $identity = new ObjectIdentity($identifier, 'any');
        $acl->expects($this->once())
            ->method('getObjectIdentity')
            ->willReturn($identity);

        return $acl;
    }

    /**
     * @return EntityAclExtension|\PHPUnit\Framework\MockObject\MockObject
     */
    private function expectsSelectAclExtension()
    {
        $extensionSelector = $this->createMock(AclExtensionSelector::class);

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
    private function expectsGetUser()
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

    private function expectsGetSid(Role $role): SecurityIdentityInterface
    {
        $sid = $this->createMock(SecurityIdentityInterface::class);

        $this->aclManager->expects($this->once())
            ->method('getSid')
            ->with($role)
            ->willReturn($sid);

        return $sid;
    }
}
