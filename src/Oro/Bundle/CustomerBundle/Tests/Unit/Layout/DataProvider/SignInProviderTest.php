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

class SignInProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var SignInProvider */
    protected $dataProvider;

    /** @var RequestStack|\PHPUnit_Framework_MockObject_MockObject */
    protected $requestStack;

    /** @var Request|\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /** @var ParameterBag|\PHPUnit_Framework_MockObject_MockObject */
    protected $parameterBag;

    /** @var TokenAccessorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $tokenAccessor;

    /** @var CsrfTokenManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $csrfTokenManager;

    /** @var SignInTargetPathProviderInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $targetPathProvider;

    protected function setUp()
    {
        $this->parameterBag = $this->createMock(ParameterBag::class);

        $this->request = $this->createMock(Request::class);
        $this->request->attributes = $this->parameterBag;

        /** @var RequestStack|\PHPUnit_Framework_MockObject_MockObject $requestStack */
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->requestStack->expects($this->any())
            ->method('getCurrentRequest')
            ->will($this->returnValue($this->request));

        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $this->csrfTokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $this->targetPathProvider = $this->createMock(SignInTargetPathProviderInterface::class);

        $this->dataProvider = new SignInProvider(
            $this->requestStack,
            $this->tokenAccessor,
            $this->csrfTokenManager,
            $this->targetPathProvider
        );
    }

    public function testGetLastNameWithSession()
    {
        /** @var SessionInterface|\PHPUnit_Framework_MockObject_MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $this->request
            ->expects($this->once())
            ->method('getSession')
            ->will($this->returnValue($session));

        $session->expects($this->once())
            ->method('get')
            ->with(Security::LAST_USERNAME)
            ->will($this->returnValue('last_name'));

        $this->assertEquals('last_name', $this->dataProvider->getLastName());
        /** test local cache */
        $this->assertEquals('last_name', $this->dataProvider->getLastName());
    }

    public function testGetLastNameWithoutSession()
    {
        $this->assertEquals('', $this->dataProvider->getLastName());
        /** test local cache */
        $this->assertEquals('', $this->dataProvider->getLastName());
    }

    public function testGetErrorWithSession()
    {
        /** @var SessionInterface|\PHPUnit_Framework_MockObject_MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $this->request
            ->expects($this->once())
            ->method('getSession')
            ->will($this->returnValue($session));

        $session->expects($this->once())
            ->method('has')
            ->with(Security::AUTHENTICATION_ERROR)
            ->will($this->returnValue(true));

        $session->expects($this->once())
            ->method('get')
            ->with(Security::AUTHENTICATION_ERROR)
            ->will($this->returnValue(new AuthenticationException('error')));

        $this->assertEquals('error', $this->dataProvider->getError());
        /** test local cache */
        $this->assertEquals('error', $this->dataProvider->getError());
    }

    public function testGetErrorWithoutSession()
    {
        /** @var SessionInterface|\PHPUnit_Framework_MockObject_MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $this->request
            ->expects($this->once())
            ->method('getSession')
            ->will($this->returnValue($session));

        $this->assertEquals('', $this->dataProvider->getError());
        /** test local cache */
        $this->assertEquals('', $this->dataProvider->getError());
    }

    public function testGetErrorFromRequestAttributes()
    {
        /** @var SessionInterface|\PHPUnit_Framework_MockObject_MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $this->request
            ->expects($this->once())
            ->method('getSession')
            ->will($this->returnValue($session));

        $this->parameterBag
            ->expects($this->once())
            ->method('has')
            ->with(Security::AUTHENTICATION_ERROR)
            ->will($this->returnValue(true));

        $this->parameterBag
            ->expects($this->once())
            ->method('get')
            ->with(Security::AUTHENTICATION_ERROR)
            ->will($this->returnValue(new AuthenticationException('error')));

        $this->assertEquals('error', $this->dataProvider->getError());
        /** test local cache */
        $this->assertEquals('error', $this->dataProvider->getError());
    }

    public function testGetCSRFToken()
    {
        /** @var CsrfToken|\PHPUnit_Framework_MockObject_MockObject $csrfToken */
        $csrfToken = $this->createMock(CsrfToken::class);
        $csrfToken->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue('csrf_token'));

        $this->csrfTokenManager
            ->expects($this->once())
            ->method('getToken')
            ->with('authenticate')
            ->will($this->returnValue($csrfToken));

        $this->assertEquals('csrf_token', $this->dataProvider->getCSRFToken());
        /** test local cache */
        $this->assertEquals('csrf_token', $this->dataProvider->getCSRFToken());
    }

    public function testGetLoggedUser()
    {
        $customerUser = new CustomerUser();

        $this->tokenAccessor->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($customerUser));

        $this->assertEquals($customerUser, $this->dataProvider->getLoggedUser());
    }

    public function testGetTargetPath()
    {
        $targetPath = 'test';

        $this->targetPathProvider->expects($this->once())
            ->method('getTargetPath')
            ->willReturn($targetPath);

        $this->assertEquals($targetPath, $this->dataProvider->getTargetPath());
    }

    public function testGetTargetPathShouldAllowNullPath()
    {
        $this->targetPathProvider->expects($this->once())
            ->method('getTargetPath')
            ->willReturn(null);

        $this->assertNull($this->dataProvider->getTargetPath());
    }
}
