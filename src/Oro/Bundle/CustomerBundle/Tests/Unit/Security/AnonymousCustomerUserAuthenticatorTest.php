<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security;

use Oro\Bundle\ApiBundle\Request\ApiRequestHelper;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitorManager;
use Oro\Bundle\CustomerBundle\Security\AnonymousCustomerUserAuthenticator;
use Oro\Bundle\CustomerBundle\Security\AnonymousCustomerUserRolesProvider;
use Oro\Bundle\CustomerBundle\Security\Badge\AnonymousCustomerUserBadge;
use Oro\Bundle\CustomerBundle\Security\Firewall\CustomerVisitorCookieFactory;
use Oro\Bundle\CustomerBundle\Security\Passport\AnonymousSelfValidatingPassport;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserTokenFactoryInterface;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Request\CsrfProtectedRequestHelper;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\Testing\Logger\BufferingLogger;
use Oro\Component\Testing\ReflectionUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class AnonymousCustomerUserAuthenticatorTest extends \PHPUnit\Framework\TestCase
{
    /** @var TokenStorageInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenStorage;

    /** @var CsrfProtectedRequestHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $csrfProtectedRequestHelper;

    /** @var CustomerVisitorCookieFactory|\PHPUnit\Framework\MockObject\MockObject */
    private $cookieFactory;

    /** @var AnonymousCustomerUserRolesProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $rolesProvider;

    /** @var ApiRequestHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $apiRequestHelper;

    /** @var BufferingLogger */
    private $logger;

    /** @var CustomerVisitorManager|\PHPUnit\Framework\MockObject\MockObject */
    private $visitorManager;

    /** @var WebsiteManager|\PHPUnit\Framework\MockObject\MockObject */
    private $websiteManager;

    /** @var AnonymousCustomerUserTokenFactoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenFactory;

    /** @var AnonymousCustomerUserAuthenticator */
    private $authenticator;

    protected function setUp(): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->csrfProtectedRequestHelper = $this->createMock(CsrfProtectedRequestHelper::class);
        $this->tokenFactory = $this->createMock(AnonymousCustomerUserTokenFactoryInterface::class);
        $this->cookieFactory = $this->createMock(CustomerVisitorCookieFactory::class);
        $this->rolesProvider = $this->createMock(AnonymousCustomerUserRolesProvider::class);
        $this->apiRequestHelper = $this->createMock(ApiRequestHelper::class);
        $this->logger = new BufferingLogger();
        $this->visitorManager = $this->createMock(CustomerVisitorManager::class);
        $this->websiteManager = $this->createMock(WebsiteManager::class);

        $this->authenticator = new AnonymousCustomerUserAuthenticator(
            $this->visitorManager,
            $this->websiteManager,
            $this->tokenStorage,
            $this->tokenFactory,
            $this->csrfProtectedRequestHelper,
            $this->cookieFactory,
            $this->rolesProvider,
            $this->apiRequestHelper,
            $this->logger,
        );
    }

    public function testSupportsForAnonymousRequest(): void
    {
        $request = new Request();
        $visitor = $this->getCustomerVisitor(1, 'someSessionId');
        $request->cookies->set(
            AnonymousCustomerUserAuthenticator::COOKIE_NAME,
            $this->getCustomerVisitorCookieValue($visitor)
        );

        self::assertTrue($this->authenticator->supports($request));
    }

    public function testSupportsForAnonymousCustomerUserTokenWithoutCredentials(): void
    {
        $request = new Request();
        self::assertTrue($this->authenticator->supports($request));
    }

    public function testSupportsForAnonymousApiRequest(): void
    {
        $request = new Request();
        $visitor = $this->getCustomerVisitor(2, 'someSessionId2');
        $request->cookies->set(
            AnonymousCustomerUserAuthenticator::COOKIE_NAME,
            $this->getCustomerVisitorCookieValue($visitor)
        );
        $this->apiRequestHelper->expects(self::once())
            ->method('isApiRequest')
            ->willReturn(true);

        self::assertFalse($this->authenticator->supports($request));
    }

    public function testSupportsWithExternalToken(): void
    {
        $request = new Request();
        $visitor = $this->getCustomerVisitor(2, 'someSessionId2');
        $request->cookies->set(
            AnonymousCustomerUserAuthenticator::COOKIE_NAME,
            $this->getCustomerVisitorCookieValue($visitor)
        );
        $this->apiRequestHelper->expects(self::never())
            ->method('isApiRequest');
        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($this->createMock(UsernamePasswordToken::class));

        self::assertFalse($this->authenticator->supports($request));
    }

    public function testSupportsForAnonymousNotProtectedCsrfRequest(): void
    {
        $request = new Request();
        $visitor = $this->getCustomerVisitor(3, 'someSessionId2');
        $request->cookies->set(
            AnonymousCustomerUserAuthenticator::COOKIE_NAME,
            $this->getCustomerVisitorCookieValue($visitor)
        );
        $this->apiRequestHelper->expects(self::once())
            ->method('isApiRequest')
            ->willReturn(true);
        $this->csrfProtectedRequestHelper->expects(self::once())
            ->method('isCsrfProtectedRequest')
            ->with(self::identicalTo($request))
            ->willReturn(false);

        self::assertFalse($this->authenticator->supports($request));
    }

    public function testAuthenticateWhenNoCurrentWebsite(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('The current website cannot be found.');

        $request = new Request();
        $this->websiteManager->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn(null);

        $this->visitorManager->expects(self::never())
            ->method('findOrCreate');

        $this->authenticator->authenticate($request);
    }

    public function testAuthenticateWhenCurrentWebsiteDoesNotHaveOrganization(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('The current website is not assigned to an organization.');
        $request = new Request();
        $this->websiteManager->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn(new Website());

        $this->visitorManager->expects(self::never())
            ->method('findOrCreate');

        $this->authenticator->authenticate($request);
    }

    public function testAuthenticateSucceess(): void
    {
        $visitor = $this->getCustomerVisitor(2, 'someSessionId2');
        $request = new Request();
        $request->cookies->set(
            AnonymousCustomerUserAuthenticator::COOKIE_NAME,
            $this->getCustomerVisitorCookieValue($visitor)
        );
        $organization = new Organization();
        $website = new Website();
        $website->setOrganization($organization);
        $this->websiteManager->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn($website);
        $this->visitorManager->expects(self::once())
            ->method('findOrCreate')
            ->with($visitor->getId(), $visitor->getSessionId())
            ->willReturn($visitor);

        $passport = $this->authenticator->authenticate($request);

        self::assertInstanceOf(AnonymousSelfValidatingPassport::class, $passport);
        self::assertTrue($passport->hasBadge(AnonymousCustomerUserBadge::class));
        self::assertNotNull($passport->getAttribute('organization'));
        self::assertTrue($request->attributes->has(AnonymousCustomerUserAuthenticator::COOKIE_ATTR_NAME));
    }

    public function testAuthenticateFailedWithCredentionalCheck(): void
    {
        $visitor = $this->getCustomerVisitor(5, 'someSessionId2');
        $request = new Request();
        $request->cookies->set(
            AnonymousCustomerUserAuthenticator::COOKIE_NAME,
            $this->getCustomerVisitorCookieValue($visitor)
        );
        $this->visitorManager
            ->method('findOrCreate')
            ->with($visitor->getId(), $visitor->getSessionId())
            ->willReturn($visitor);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('The current website cannot be found.');

        $this->authenticator->authenticate($request);
        self::assertNull($request->attributes->get(AnonymousCustomerUserAuthenticator::COOKIE_ATTR_NAME));
    }

    public function testCreateToken(): void
    {
        $visitor = $this->getCustomerVisitor(5, 'someSessionId2');
        $credentionals = $this->getCustomerVisitorCookieValue($visitor);
        $passport = new AnonymousSelfValidatingPassport(
            new AnonymousCustomerUserBadge($credentionals, [$this->authenticator, 'getVisitor']),
        );
        $passport->setAttribute('organization', new Organization());
        $this->visitorManager
            ->method('findOrCreate')
            ->with($visitor->getId(), $visitor->getSessionId())
            ->willReturn($visitor);

        $token = $this->authenticator->createToken($passport, 'test');

        self::assertInstanceOf(AnonymousCustomerUserToken::class, $token);
        self::assertEquals(
            [
                ['info', 'Populated the TokenStorage with an Anonymous Customer User Token.', []]
            ],
            $this->logger->cleanLogs()
        );
    }

    public function testGetVisitorWithInvalidCredentials(): void
    {
        $encodedBrokenCredentials = base64_encode(json_encode([])) . 'SomeBroken';
        $visitor = $this->getCustomerVisitor(1, 'someSessionId');

        $this->visitorManager->expects(self::once())
            ->method('findOrCreate')
            ->with(null, null)
            ->willReturn($visitor);

        self::assertEquals(
            $visitor,
            $this->authenticator->getVisitor($encodedBrokenCredentials)
        );
    }

    public function testGetVisitorWithValidCredentials(): void
    {
        $sessionId = 'sessionId';
        $visitorId = '123';

        $credentials = [
            $visitorId,
            $sessionId
        ];

        $encodedCredentials = base64_encode(json_encode($credentials));
        $visitor = $this->getCustomerVisitor(123, 'sessionId');
        $this->visitorManager->expects(self::once())
            ->method('findOrCreate')
            ->with('123', 'sessionId')
            ->willReturn($visitor);

        $foundVisitor = $this->authenticator->getVisitor($encodedCredentials);

        self::assertEquals($visitorId, $foundVisitor->getId());
        self::assertEquals($sessionId, $foundVisitor->getSessionId());
    }

    private function getCustomerVisitorCookieValue(CustomerVisitor $visitor): string
    {
        return base64_encode(json_encode([$visitor->getId(), $visitor->getSessionId()], JSON_THROW_ON_ERROR));
    }

    private function getCustomerVisitor(int $id, string $sessionId): CustomerVisitor
    {
        $visitor = new CustomerVisitor();
        ReflectionUtil::setId($visitor, $id);
        $visitor->setSessionId($sessionId);

        return $visitor;
    }
}
