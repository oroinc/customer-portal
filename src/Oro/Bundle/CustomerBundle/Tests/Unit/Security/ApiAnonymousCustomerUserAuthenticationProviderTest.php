<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security;

use Oro\Bundle\CustomerBundle\Model\InMemoryCustomerVisitor;
use Oro\Bundle\CustomerBundle\Security\ApiAnonymousCustomerUserAuthenticationProvider;
use Oro\Bundle\CustomerBundle\Security\Token\ApiAnonymousCustomerUserToken;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class ApiAnonymousCustomerUserAuthenticationProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var WebsiteManager|\PHPUnit\Framework\MockObject\MockObject */
    private $websiteManager;

    /** @var ApiAnonymousCustomerUserAuthenticationProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->websiteManager = $this->createMock(WebsiteManager::class);

        $this->provider = new ApiAnonymousCustomerUserAuthenticationProvider($this->websiteManager);
    }

    public function testSupportsForNotApiAnonymousCustomerUserToken(): void
    {
        self::assertFalse($this->provider->supports($this->createMock(TokenInterface::class)));
    }

    public function testSupportsForApiAnonymousCustomerUserTokenWithoutCredentials(): void
    {
        self::assertTrue($this->provider->supports(new ApiAnonymousCustomerUserToken('User')));
    }

    public function testSupportsForApiAnonymousCustomerUserTokenWithCredentials(): void
    {
        $token = new ApiAnonymousCustomerUserToken('User');
        $token->setCredentials(['visitor_id'=> 1, 'session_id'=> 'test_session']);
        self::assertFalse($this->provider->supports($token));
    }

    public function testAuthenticateWhenNoCurrentWebsite(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('The current website cannot be found.');

        $token = new ApiAnonymousCustomerUserToken('User', ['ROLE_FOO', 'ROLE_BAR']);

        $this->websiteManager->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn(null);

        $this->provider->authenticate($token);
    }

    public function testAuthenticateWhenCurrentWebsiteDoesNotHaveOrganization(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('The current website is not assigned to an organization.');

        $token = new ApiAnonymousCustomerUserToken('User', ['ROLE_FOO', 'ROLE_BAR']);

        $this->websiteManager->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn(new Website());

        $this->provider->authenticate($token);
    }

    public function testAuthenticate(): void
    {
        $token = new ApiAnonymousCustomerUserToken('User', ['ROLE_FOO', 'ROLE_BAR']);

        $organization = new Organization();
        $website = new Website();
        $website->setOrganization($organization);

        $this->websiteManager->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $authenticatedToken = $this->provider->authenticate($token);
        self::assertInstanceOf(ApiAnonymousCustomerUserToken::class, $authenticatedToken);
        self::assertInstanceOf(InMemoryCustomerVisitor::class, $authenticatedToken->getVisitor());
        self::assertEquals(
            new ApiAnonymousCustomerUserToken(
                'User',
                ['ROLE_FOO', 'ROLE_BAR'],
                $authenticatedToken->getVisitor(),
                $organization
            ),
            $authenticatedToken
        );
    }
}
