<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Acl\Group;

use Oro\Bundle\CustomerBundle\Acl\Group\AclGroupProvider;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\SecurityBundle\Authentication\Token\OrganizationToken;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\User;

class AclGroupProviderTest extends \PHPUnit\Framework\TestCase
{
    const LOCAL_LEVEL = 'Oro\Bundle\CustomerBundle\Entity\Customer';
    const BASIC_LEVEL = 'Oro\Bundle\CustomerBundle\Entity\CustomerUser';

    /** @var \PHPUnit\Framework\MockObject\MockObject|TokenAccessorInterface */
    protected $tokenAccessor;

    /** @var AclGroupProvider */
    protected $provider;

    protected function setUp()
    {
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->provider = new AclGroupProvider($this->tokenAccessor);
    }

    protected function tearDown()
    {
        unset($this->tokenAccessor, $this->provider);
    }

    public function testSupportsAnonymous()
    {
        $this->tokenAccessor->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $token = $this->createMock(AnonymousCustomerUserToken::class);
        $this->tokenAccessor->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->assertTrue($this->provider->supports());
    }

    public function testSupportsCustomerUser()
    {
        $this->tokenAccessor->expects($this->once())
            ->method('getUser')
            ->willReturn(new CustomerUser());

        $token = $this->createMock(OrganizationToken::class);
        $this->tokenAccessor->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->assertTrue($this->provider->supports());
    }

    public function testSupportsNoTokenNoUser()
    {
        $this->tokenAccessor->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $this->tokenAccessor->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        $this->assertFalse($this->provider->supports());
    }

    public function testSupportsUnsupportedUser()
    {
        $this->tokenAccessor->expects($this->once())
            ->method('getUser')
            ->willReturn($this->createMock(User::class));

        $token = $this->createMock(OrganizationToken::class);
        $this->tokenAccessor->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->assertFalse($this->provider->supports());
    }

    public function testGetGroup()
    {
        $this->assertEquals(CustomerUser::SECURITY_GROUP, $this->provider->getGroup());
    }
}
