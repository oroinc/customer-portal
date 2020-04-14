<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Acl\Resolver;

use Oro\Bundle\CustomerBundle\Acl\Resolver\RoleTranslationPrefixResolver;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserInterface;

class RoleTranslationPrefixResolverTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|TokenAccessorInterface */
    protected $tokenAccessor;

    /** @var RoleTranslationPrefixResolver */
    protected $resolver;

    protected function setUp(): void
    {
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->resolver = new RoleTranslationPrefixResolver($this->tokenAccessor);
    }

    protected function tearDown(): void
    {
        unset($this->resolver);
    }

    /**
     * @dataProvider getPrefixDataProvider
     *
     * @param UserInterface|string|null $loggedUser
     * @param string|null $expectedPrefix
     */
    public function testGetPrefix($loggedUser, $expectedPrefix = null)
    {
        $this->tokenAccessor->expects($this->once())
            ->method('getUser')
            ->willReturn($loggedUser);

        if (!$expectedPrefix) {
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('This method must be called only for logged User or CustomerUser');
        }

        $this->assertEquals($expectedPrefix, $this->resolver->getPrefix());
    }

    /**
     * @return array
     */
    public function getPrefixDataProvider()
    {
        return [
            [new User, RoleTranslationPrefixResolver::BACKEND_PREFIX],
            [new CustomerUser(), RoleTranslationPrefixResolver::FRONTEND_PREFIX],
            ['anon.'],
            [null]
        ];
    }
}
