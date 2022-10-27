<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Layout\DataProvider;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Layout\DataProvider\SignInProvider;
use Oro\Bundle\CustomerBundle\Layout\DataProvider\SignInTargetPathProviderInterface;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class SignInProviderTest extends \PHPUnit\Framework\TestCase
{
    private SignInProvider $dataProvider;

    /** @var RequestStack|\PHPUnit\Framework\MockObject\MockObject */
    private RequestStack $requestStack;

    /** @var Request|\PHPUnit\Framework\MockObject\MockObject */
    private Request $request;

    /** @var ParameterBag|\PHPUnit\Framework\MockObject\MockObject */
    private ParameterBag $parameterBag;

    /** @var TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private TokenAccessorInterface $tokenAccessor;

    /** @var CsrfTokenManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private CsrfTokenManagerInterface $csrfTokenManager;

    /** @var SignInTargetPathProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private SignInTargetPathProviderInterface $targetPathProvider;

    /** @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private TranslatorInterface $translator;

    protected function setUp(): void
    {
        $this->parameterBag = $this->createMock(ParameterBag::class);

        $this->request = $this->createMock(Request::class);
        $this->request->attributes = $this->parameterBag;

        $this->requestStack = $this->createMock(RequestStack::class);
        $this->requestStack->expects(self::any())
            ->method('getCurrentRequest')
            ->willReturn($this->request);

        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $this->csrfTokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $this->targetPathProvider = $this->createMock(SignInTargetPathProviderInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->dataProvider = new SignInProvider(
            $this->requestStack,
            $this->tokenAccessor,
            $this->csrfTokenManager,
            $this->targetPathProvider,
            $this->translator
        );
    }

    public function testGetLastNameWithSession(): void
    {
        $session = $this->createMock(SessionInterface::class);
        $this->request->expects(self::once())
            ->method('hasSession')
            ->willReturn(true);
        $this->request->expects(self::once())
            ->method('getSession')
            ->willReturn($session);

        $session->expects(self::once())
            ->method('get')
            ->with(Security::LAST_USERNAME)
            ->willReturn('last_name');

        self::assertEquals('last_name', $this->dataProvider->getLastName());
        /** test local cache */
        self::assertEquals('last_name', $this->dataProvider->getLastName());
    }

    public function testGetLastNameWithoutSession(): void
    {
        self::assertEquals('', $this->dataProvider->getLastName());
        /** test local cache */
        self::assertEquals('', $this->dataProvider->getLastName());
    }

    public function testGetErrorWithSession(): void
    {
        $session = $this->createMock(SessionInterface::class);
        $this->request->expects(self::once())
            ->method('hasSession')
            ->willReturn(true);
        $this->request->expects(self::once())
            ->method('getSession')
            ->willReturn($session);

        $session->expects(self::once())
            ->method('has')
            ->with(Security::AUTHENTICATION_ERROR)
            ->willReturn(true);

        $exception = new AuthenticationException('error');
        $translatedErrorMessage = 'trans error';
        $this->translator->expects(self::once())
            ->method('trans')
            ->with($exception->getMessageKey(), $exception->getMessageData(), 'security')
            ->willReturn($translatedErrorMessage);

        $session->expects(self::once())
            ->method('get')
            ->with(Security::AUTHENTICATION_ERROR)
            ->willReturn($exception);

        self::assertEquals($translatedErrorMessage, $this->dataProvider->getError());
        /** test local cache */
        self::assertEquals($translatedErrorMessage, $this->dataProvider->getError());
    }

    public function testGetErrorWithoutError(): void
    {
        $session = $this->createMock(SessionInterface::class);
        $this->request->expects(self::once())
            ->method('hasSession')
            ->willReturn(true);
        $this->request->expects(self::once())
            ->method('getSession')
            ->willReturn($session);

        self::assertEquals('', $this->dataProvider->getError());
        /** test local cache */
        self::assertEquals('', $this->dataProvider->getError());
    }

    public function testGetErrorWithoutSession(): void
    {
        $this->request->expects(self::once())
            ->method('hasSession')
            ->willReturn(false);
        $this->request->expects(self::never())
            ->method('getSession');

        self::assertEquals('', $this->dataProvider->getError());
        /** test local cache */
        self::assertEquals('', $this->dataProvider->getError());
    }

    public function testGetErrorFromRequestAttributes(): void
    {
        $this->request->expects(self::never())
            ->method('getSession');

        $this->parameterBag->expects(self::once())
            ->method('has')
            ->with(Security::AUTHENTICATION_ERROR)
            ->willReturn(true);

        $exception = new AuthenticationException('error');
        $translatedErrorMessage = 'trans error';
        $this->translator->expects(self::once())
            ->method('trans')
            ->with($exception->getMessageKey(), $exception->getMessageData(), 'security')
            ->willReturn($translatedErrorMessage);

        $this->parameterBag->expects(self::once())
            ->method('get')
            ->with(Security::AUTHENTICATION_ERROR)
            ->willReturn($exception);

        self::assertEquals($translatedErrorMessage, $this->dataProvider->getError());
        /** test local cache */
        self::assertEquals($translatedErrorMessage, $this->dataProvider->getError());
    }

    public function testGetErrorWhenNotAuthenticationExceptionOccurred(): void
    {
        $this->parameterBag->expects(self::once())
            ->method('has')
            ->with(Security::AUTHENTICATION_ERROR)
            ->willReturn(true);

        $exception = new \Exception('error');
        $this->translator->expects(self::never())
            ->method('trans');

        $this->parameterBag->expects(self::once())
            ->method('get')
            ->with(Security::AUTHENTICATION_ERROR)
            ->willReturn($exception);

        self::assertEquals($exception->getMessage(), $this->dataProvider->getError());
        /** test local cache */
        self::assertEquals($exception->getMessage(), $this->dataProvider->getError());
    }

    public function testGetCSRFToken(): void
    {
        $csrfToken = $this->createMock(CsrfToken::class);
        $csrfToken->expects(self::once())
            ->method('getValue')
            ->willReturn('csrf_token');

        $this->csrfTokenManager->expects(self::once())
            ->method('getToken')
            ->with('authenticate')
            ->willReturn($csrfToken);

        self::assertEquals('csrf_token', $this->dataProvider->getCSRFToken());
        /** test local cache */
        self::assertEquals('csrf_token', $this->dataProvider->getCSRFToken());
    }

    public function testGetLoggedUser(): void
    {
        $customerUser = new CustomerUser();

        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn($customerUser);

        self::assertEquals($customerUser, $this->dataProvider->getLoggedUser());
    }

    public function testGetTargetPath(): void
    {
        $targetPath = 'test';

        $this->targetPathProvider->expects(self::once())
            ->method('getTargetPath')
            ->willReturn($targetPath);

        self::assertEquals($targetPath, $this->dataProvider->getTargetPath());
    }

    public function testGetTargetPathShouldAllowNullPath(): void
    {
        $this->targetPathProvider->expects(self::once())
            ->method('getTargetPath')
            ->willReturn(null);

        self::assertNull($this->dataProvider->getTargetPath());
    }
}
