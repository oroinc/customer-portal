<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Provider;

use Oro\Bundle\FrontendBundle\Provider\FrontendCurrentApplicationProvider;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\UserBundle\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class FrontendCurrentApplicationProviderTest extends TestCase
{
    private TokenStorageInterface&MockObject $tokenStorage;
    private FrontendHelper&MockObject $frontendHelper;
    private FrontendCurrentApplicationProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->frontendHelper = $this->createMock(FrontendHelper::class);

        $this->provider = new FrontendCurrentApplicationProvider(
            $this->tokenStorage,
            $this->frontendHelper
        );
    }

    public function testIsApplicationsValidForEmptyApplicationsAndBackendRequest(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->assertTrue($this->provider->isApplicationsValid([]));
    }

    public function testIsApplicationsValidForEmptyApplicationsAndFrontendRequest(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->assertFalse($this->provider->isApplicationsValid([]));
    }

    /**
     * @dataProvider validFrontendApplicationsProvider
     */
    public function testIsApplicationsValidForFrontendRequestAndApplicationsHasValidApplication(
        array $applications
    ): void {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);
        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($this->createMock(TokenInterface::class));

        $this->assertTrue($this->provider->isApplicationsValid($applications));
    }

    public function validFrontendApplicationsProvider(): array
    {
        return [
            'storefront only' => [
                [FrontendCurrentApplicationProvider::COMMERCE_APPLICATION],
            ],
            'back-office and storefront' => [
                [
                    FrontendCurrentApplicationProvider::DEFAULT_APPLICATION,
                    FrontendCurrentApplicationProvider::COMMERCE_APPLICATION,
                ],
            ],
        ];
    }

    public function testIsApplicationsValidForFrontendRequestWithoutTokenAndApplicationsHasValidApplication(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);
        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn(null);

        $this->assertFalse(
            $this->provider->isApplicationsValid([
                FrontendCurrentApplicationProvider::COMMERCE_APPLICATION
            ])
        );
    }

    public function testIsApplicationsValidForFrontendRequestAndApplicationsDoesNotHaveValidApplication(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);
        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($this->createMock(TokenInterface::class));

        $this->assertFalse(
            $this->provider->isApplicationsValid([
                FrontendCurrentApplicationProvider::DEFAULT_APPLICATION
            ])
        );
    }

    public function testIsApplicationsValidForFrontendRequestWithoutTokenAndApplicationsDoesNotHaveValidApp(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);
        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn(null);

        $this->assertFalse(
            $this->provider->isApplicationsValid([
                FrontendCurrentApplicationProvider::DEFAULT_APPLICATION
            ])
        );
    }

    /**
     * @dataProvider validBackendApplicationsProvider
     */
    public function testIsApplicationsValidForBackendRequestAndApplicationsHasValidApplication(
        array $applications
    ): void {
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

        $this->assertTrue($this->provider->isApplicationsValid($applications));
    }

    public function validBackendApplicationsProvider(): array
    {
        return [
            'back-office only' => [
                [FrontendCurrentApplicationProvider::DEFAULT_APPLICATION],
            ],
            'back-office and storefront' => [
                [
                    FrontendCurrentApplicationProvider::DEFAULT_APPLICATION,
                    FrontendCurrentApplicationProvider::COMMERCE_APPLICATION,
                ],
            ],
        ];
    }

    public function testIsApplicationsValidForBackendRequestAndApplicationsDoesNotHaveValidApplication(): void
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
                FrontendCurrentApplicationProvider::COMMERCE_APPLICATION
            ])
        );
    }

    public function testGetCurrentApplicationForFrontendRequest(): void
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

    public function testGetCurrentApplicationForFrontendRequestWithoutToken(): void
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

    public function testGetCurrentApplicationForBackendRequest(): void
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
