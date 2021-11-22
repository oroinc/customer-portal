<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Provider;

use Oro\Bundle\FrontendBundle\Provider\FrontendCurrentApplicationProvider;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class FrontendCurrentApplicationProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|TokenStorageInterface */
    private $tokenStorage;

    /** @var \PHPUnit\Framework\MockObject\MockObject|FrontendHelper */
    private $frontendHelper;

    /** @var FrontendCurrentApplicationProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->frontendHelper = $this->createMock(FrontendHelper::class);

        $this->provider = new FrontendCurrentApplicationProvider(
            $this->tokenStorage,
            $this->frontendHelper
        );
    }

    public function testIsApplicationsValidForEmptyApplications()
    {
        $this->assertTrue($this->provider->isApplicationsValid([]));
    }

    public function testIsApplicationsValidForFrontendRequestAndApplicationsHasValidApplication()
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);
        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($this->createMock(TokenInterface::class));

        $this->assertTrue(
            $this->provider->isApplicationsValid([
                FrontendCurrentApplicationProvider::COMMERCE_APPLICATION,
                'another'
            ])
        );
    }

    public function testIsApplicationsValidForFrontendRequestWithoutTokenAndApplicationsHasValidApplication()
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);
        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn(null);

        $this->assertFalse(
            $this->provider->isApplicationsValid([
                FrontendCurrentApplicationProvider::COMMERCE_APPLICATION,
                'another'
            ])
        );
    }

    public function testIsApplicationsValidForFrontendRequestAndApplicationsDoesNotHaveValidApplication()
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);
        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($this->createMock(TokenInterface::class));

        $this->assertFalse(
            $this->provider->isApplicationsValid([
                'another'
            ])
        );
    }

    public function testIsApplicationsValidForFrontendRequestWithoutTokenAndApplicationsDoesNotHaveValidApplication()
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);
        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn(null);

        $this->assertFalse(
            $this->provider->isApplicationsValid([
                'another'
            ])
        );
    }

    public function testIsApplicationsValidForBackendRequestAndApplicationsHasValidApplication()
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::once())
            ->method('getUser')
            ->willReturn($this->createMock(User::class));

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);
        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);

        $this->assertTrue(
            $this->provider->isApplicationsValid([
                FrontendCurrentApplicationProvider::DEFAULT_APPLICATION,
                'another'
            ])
        );
    }

    public function testIsApplicationsValidForBackendRequestAndApplicationsDoesNotHaveValidApplication()
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::once())
            ->method('getUser')
            ->willReturn($this->createMock(User::class));

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);
        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);

        $this->assertFalse(
            $this->provider->isApplicationsValid([
                'another'
            ])
        );
    }

    public function testGetCurrentApplicationForFrontendRequest()
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);
        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($this->createMock(TokenInterface::class));

        $this->assertEquals(
            FrontendCurrentApplicationProvider::COMMERCE_APPLICATION,
            $this->provider->getCurrentApplication()
        );
    }

    public function testGetCurrentApplicationForFrontendRequestWithoutToken()
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);
        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn(null);

        $this->assertNull(
            $this->provider->getCurrentApplication()
        );
    }

    public function testGetCurrentApplicationForBackendRequest()
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::once())
            ->method('getUser')
            ->willReturn($this->createMock(User::class));

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);
        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);

        $this->assertEquals(
            FrontendCurrentApplicationProvider::DEFAULT_APPLICATION,
            $this->provider->getCurrentApplication()
        );
    }
}
