<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\GuestAccess;

use Oro\Bundle\FrontendBundle\GuestAccess\GuestAccessDecisionMaker;
use Oro\Bundle\FrontendBundle\GuestAccess\GuestAccessDecisionMakerInterface;
use Oro\Bundle\FrontendBundle\GuestAccess\Provider\GuestAccessAllowedUrlsProviderInterface;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GuestAccessDecisionMakerTest extends TestCase
{
    private const ALLOWED_URLS = [
        '^/customer/user/login$',
        '^/customer/user/registration$'
    ];

    private const NOT_FRONTEND_URL = '/admin/';

    private FrontendHelper&MockObject $frontendHelper;
    private GuestAccessAllowedUrlsProviderInterface&MockObject $guestAccessAllowedUrlsProvider;
    private GuestAccessDecisionMaker $guestAccessDecisionMaker;

    #[\Override]
    protected function setUp(): void
    {
        $this->frontendHelper = $this->createMock(FrontendHelper::class);
        $this->guestAccessAllowedUrlsProvider = $this->createMock(GuestAccessAllowedUrlsProviderInterface::class);

        $this->guestAccessDecisionMaker = new GuestAccessDecisionMaker(
            $this->guestAccessAllowedUrlsProvider,
            $this->frontendHelper
        );
    }

    /**
     * @dataProvider decideDataProvider
     */
    public function testDecide(string $url, int $expectedDecision): void
    {
        $this->guestAccessAllowedUrlsProvider->expects(self::any())
            ->method('getAllowedUrlsPatterns')
            ->willReturn(self::ALLOWED_URLS);

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendUrl')
            ->willReturnCallback(function ($urlToCheck) {
                return self::NOT_FRONTEND_URL !== $urlToCheck;
            });

        $decision = $this->guestAccessDecisionMaker->decide($url);
        self::assertSame($expectedDecision, $decision);
    }

    public function decideDataProvider(): array
    {
        return [
            'not frontend url' => ['/admin/', GuestAccessDecisionMakerInterface::URL_ALLOW],
            'allowed login url' => ['/customer/user/login', GuestAccessDecisionMakerInterface::URL_ALLOW],
            'allowed registration url' => ['/customer/user/registration', GuestAccessDecisionMakerInterface::URL_ALLOW],
            'disallowed inner url' => ['/customer/user/login/inner', GuestAccessDecisionMakerInterface::URL_DISALLOW],
            'disallowed parent url' => ['/customer/user', GuestAccessDecisionMakerInterface::URL_DISALLOW],
            'disallowed random url' => ['/some-random-url', GuestAccessDecisionMakerInterface::URL_DISALLOW],
        ];
    }
}
