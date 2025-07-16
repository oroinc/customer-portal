<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Layout\DataProvider;

use Oro\Bundle\CustomerBundle\Layout\DataProvider\SignInTargetPathProvider;
use Oro\Bundle\CustomerBundle\Provider\RedirectAfterLoginProvider;
use Oro\Bundle\SecurityBundle\Util\SameSiteUrlHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;

final class SignInTargetPathProviderTest extends TestCase
{
    private RedirectAfterLoginProvider&MockObject $redirectTargetPageProvider;
    private SameSiteUrlHelper&MockObject $sameSiteUrlHelper;
    private RouterInterface&MockObject $router;
    private SignInTargetPathProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->redirectTargetPageProvider = self::createMock(RedirectAfterLoginProvider::class);
        $this->sameSiteUrlHelper = self::createMock(SameSiteUrlHelper::class);
        $this->router = self::createMock(RouterInterface::class);

        $this->provider = new SignInTargetPathProvider(
            $this->redirectTargetPageProvider,
            $this->sameSiteUrlHelper,
            $this->router
        );
    }

    public function testGetRootPath(): void
    {
        $this->provider->addExcludedRoute('excluded_route');
        $this->redirectTargetPageProvider->expects(self::once())
            ->method('getRedirectTargetUrl')
            ->willReturn('/excluded/route');

        $this->sameSiteUrlHelper->expects(self::never())
            ->method('isSameSiteUrl');

        $this->router->expects(self::once())
            ->method('match')
            ->with('/excluded/route')
            ->willReturn(['_route' => 'excluded_route']);

        $this->router->expects(self::once())
            ->method('generate')
            ->with('oro_frontend_root')
            ->willReturn('/root');

        self::assertEquals('/root', $this->provider->getTargetPath());
    }

    public function testGetTargetPath(): void
    {
        $this->redirectTargetPageProvider->expects(self::once())
            ->method('getRedirectTargetUrl')
            ->willReturn('/customer/profile');

        $this->sameSiteUrlHelper->expects(self::once())
            ->method('isSameSiteUrl')
            ->with('/customer/profile')
            ->willReturn(true);

        self::assertEquals('/customer/profile', $this->provider->getTargetPath());
    }

    public function testGetTargetPathWhenTargetUrlIsNull(): void
    {
        $this->redirectTargetPageProvider->expects(self::once())
            ->method('getRedirectTargetUrl')
            ->willReturn(null);

        $this->sameSiteUrlHelper->expects(self::never())
            ->method('isSameSiteUrl');

        self::assertNull($this->provider->getTargetPath());
    }

    public function testGetTargetPathNoSameSite(): void
    {
        $this->redirectTargetPageProvider->expects(self::once())
            ->method('getRedirectTargetUrl')
            ->willReturn('ftp://test.com/customer/profile');

        $this->sameSiteUrlHelper->expects(self::once())
            ->method('isSameSiteUrl')
            ->with('ftp://test.com/customer/profile')
            ->willReturn(false);

        self::assertNull($this->provider->getTargetPath());
    }
}
