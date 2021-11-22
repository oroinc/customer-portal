<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\GuestAccess;

use Oro\Bundle\FrontendBundle\GuestAccess\GuestAccessDecisionMaker;
use Oro\Bundle\FrontendBundle\GuestAccess\GuestAccessDecisionMakerInterface;
use Oro\Bundle\FrontendBundle\GuestAccess\Provider\GuestAccessAllowedUrlsProviderInterface;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;

class GuestAccessDecisionMakerTest extends \PHPUnit\Framework\TestCase
{
    private const ALLOWED_URLS = [
        '^/customer/user/login$',
        '^/customer/user/registration$'
    ];

    private const NOT_FRONTEND_URL = '/admin/';

    /** @var FrontendHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $frontendHelper;

    /** @var GuestAccessAllowedUrlsProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $guestAccessAllowedUrlsProvider;

    /** @var GuestAccessDecisionMaker */
    private $guestAccessDecisionMaker;

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
    public function testDecide(string $url, int $expectedDecision)
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
