<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security;

use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitorManager;
use Oro\Bundle\CustomerBundle\Security\AnonymousCustomerUserAuthenticationProvider;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\Testing\ReflectionUtil;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AnonymousCustomerUserAuthenticationProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var CustomerVisitorManager|\PHPUnit\Framework\MockObject\MockObject */
    private $visitorManager;

    /** @var WebsiteManager|\PHPUnit\Framework\MockObject\MockObject */
    private $websiteManager;

    /** @var AnonymousCustomerUserAuthenticationProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->visitorManager = $this->createMock(CustomerVisitorManager::class);
        $this->websiteManager = $this->createMock(WebsiteManager::class);

        $this->provider = new AnonymousCustomerUserAuthenticationProvider(
            $this->visitorManager,
            $this->websiteManager
        );
    }

    public function testSupportsForNotAnonymousCustomerUserToken(): void
    {
        self::assertFalse($this->provider->supports($this->createMock(TokenInterface::class)));
    }

    public function testSupportsForAnonymousCustomerUserTokenWithoutCredentials(): void
    {
        self::assertFalse($this->provider->supports(new AnonymousCustomerUserToken('User')));
    }

    public function testSupportsForAnonymousCustomerUserTokenWithCredentials(): void
    {
        $token = new AnonymousCustomerUserToken('User');
        $token->setCredentials(['visitor_id'=> 1, 'session_id'=> 'test_session']);
        self::assertTrue($this->provider->supports($token));
    }

    public function testAuthenticateWhenNoCurrentWebsite(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('The current website cannot be found.');

        $token = new AnonymousCustomerUserToken('User', ['ROLE_FOO', 'ROLE_BAR']);
        $token->setCredentials(['visitor_id'=> 1, 'session_id'=> 'test_session']);

        $this->websiteManager->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn(null);

        $this->visitorManager->expects(self::never())
            ->method('findOrCreate');

        $this->provider->authenticate($token);
    }

    public function testAuthenticateWhenCurrentWebsiteDoesNotHaveOrganization(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('The current website is not assigned to an organization.');

        $token = new AnonymousCustomerUserToken('User', ['ROLE_FOO', 'ROLE_BAR']);
        $token->setCredentials(['visitor_id'=> 1, 'session_id'=> 'test_session']);

        $this->websiteManager->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn(new Website());

        $this->visitorManager->expects(self::never())
            ->method('findOrCreate');

        $this->provider->authenticate($token);
    }

    public function testAuthenticate(): void
    {
        $entityId = 1;
        $sessionId = 'test_session';

        $token = new AnonymousCustomerUserToken('User', ['ROLE_FOO', 'ROLE_BAR']);
        $token->setCredentials(['visitor_id'=> $entityId, 'session_id'=> $sessionId]);

        $visitor = new CustomerVisitor();
        ReflectionUtil::setId($visitor, $entityId);
        $visitor->setSessionId($sessionId);

        $organization = new Organization();
        $website = new Website();
        $website->setOrganization($organization);

        $this->websiteManager->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $this->visitorManager->expects(self::once())
            ->method('findOrCreate')
            ->with($entityId, $sessionId)
            ->willReturn($visitor);

        self::assertEquals(
            new AnonymousCustomerUserToken(
                'User',
                ['ROLE_FOO', 'ROLE_BAR'],
                $visitor,
                $organization
            ),
            $this->provider->authenticate($token)
        );
    }
}
