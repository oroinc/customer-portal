<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security;

use Oro\Bundle\ApiBundle\Request\ApiRequestHelper;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Model\InMemoryCustomerVisitor;
use Oro\Bundle\CustomerBundle\Security\AnonymousCustomerUserRolesProvider;
use Oro\Bundle\CustomerBundle\Security\ApiAnonymousCustomerUserAuthenticator;
use Oro\Bundle\CustomerBundle\Security\Badge\AnonymousCustomerUserBadge;
use Oro\Bundle\CustomerBundle\Security\Firewall\ApiAnonymousCustomerUserAuthenticationDecisionMaker;
use Oro\Bundle\CustomerBundle\Security\Passport\AnonymousSelfValidatingPassport;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserTokenFactoryInterface;
use Oro\Bundle\CustomerBundle\Security\Token\ApiAnonymousCustomerUserToken;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class ApiAnonymousCustomerUserAuthenticatorTest extends \PHPUnit\Framework\TestCase
{
    /** @var WebsiteManager|\PHPUnit\Framework\MockObject\MockObject */
    private $websiteManager;

    /** @var ApiAnonymousCustomerUserAuthenticationDecisionMaker|\PHPUnit\Framework\MockObject\MockObject */
    private $decisionMaker;

    /** @var ApiRequestHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $apiRequestHelper;

    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject */
    private $configManager;

    /** @var ApiAnonymousCustomerUserAuthenticator */
    private $authenticator;

    protected function setUp(): void
    {
        $this->websiteManager = $this->createMock(WebsiteManager::class);
        $this->decisionMaker = $this->createMock(ApiAnonymousCustomerUserAuthenticationDecisionMaker::class);
        $this->apiRequestHelper = $this->createMock(ApiRequestHelper::class);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->authenticator = new ApiAnonymousCustomerUserAuthenticator(
            $this->websiteManager,
            $this->createMock(TokenStorage::class),
            $this->createMock(AnonymousCustomerUserTokenFactoryInterface::class),
            $this->createMock(AnonymousCustomerUserRolesProvider::class),
            $this->apiRequestHelper,
            $this->configManager,
            $this->decisionMaker,
            $this->createMock(LoggerInterface::class),
        );
    }

    public function testSupportsForNotApiAnonymousCustomerUserRequest(): void
    {
        self::assertFalse($this->authenticator->supports(new Request()));
    }

    private function getCustomerVisitorCookieValue(CustomerVisitor $visitor): string
    {
        return base64_encode(json_encode([$visitor->getId(), $visitor->getSessionId()], JSON_THROW_ON_ERROR));
    }

    public function testSupportsForApiAnonymousCustomerUserTokenWithoutCredentials(): void
    {
        $request = Request::create('http://test.com/api/test');
        $this->decisionMaker->expects($this->once())
            ->method('isAnonymousCustomerUserAllowed')
            ->willReturn(true);
        $this->apiRequestHelper->expects($this->once())
            ->method('isApiRequest')
            ->willReturn(true);
        $this->configManager->expects($this->once())
            ->method('get')
            ->willReturn(true);

        self::assertTrue($this->authenticator->supports($request));
    }

    public function testAuthenticateWhenNoCurrentWebsite(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('The current website cannot be found.');

        $request = Request::create('http://test.com/api/test');

        $this->websiteManager->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn(null);

        $this->authenticator->authenticate($request);
    }

    public function testAuthenticateWhenCurrentWebsiteDoesNotHaveOrganization(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('The current website is not assigned to an organization.');

        $request = Request::create('http://test.com/api/test');

        $this->websiteManager->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn(new Website());

        $this->authenticator->authenticate($request);
    }

    public function testAuthenticate(): void
    {
        $request = Request::create('http://test.com/api/test');
        $organization = new Organization();
        $website = new Website();
        $website->setOrganization($organization);

        $this->websiteManager->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $passport = $this->authenticator->authenticate($request);
        self::assertInstanceOf(AnonymousSelfValidatingPassport::class, $passport);
        self::assertTrue($passport->hasBadge(AnonymousCustomerUserBadge::class));
        self::assertNotNull($passport->getAttribute('organization'));
    }

    public function testCreateToken(): void
    {
        $passport = new AnonymousSelfValidatingPassport(
            new AnonymousCustomerUserBadge('', fn () => new InMemoryCustomerVisitor()),
        );
        $passport->setAttribute('organization', new Organization());
        $token = $this->authenticator->createToken($passport, 'test');

        self::assertInstanceOf(ApiAnonymousCustomerUserToken::class, $token);
    }
}
