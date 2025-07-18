<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Acl\Resolver;

use Oro\Bundle\CustomerBundle\Acl\Resolver\RoleTranslationPrefixResolver;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RoleTranslationPrefixResolverTest extends TestCase
{
    private TokenAccessorInterface&MockObject $tokenAccessor;
    private RoleTranslationPrefixResolver $resolver;

    #[\Override]
    protected function setUp(): void
    {
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->resolver = new RoleTranslationPrefixResolver($this->tokenAccessor);
    }

    /**
     * @dataProvider getPrefixDataProvider
     */
    public function testGetPrefix(UserInterface|string|null $loggedUser, ?string $expectedPrefix = null): void
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

    public function getPrefixDataProvider(): array
    {
        return [
            [new User(), RoleTranslationPrefixResolver::BACKEND_PREFIX],
            [new CustomerUser(), RoleTranslationPrefixResolver::FRONTEND_PREFIX],
            ['anon.'],
            [null]
        ];
    }
}
