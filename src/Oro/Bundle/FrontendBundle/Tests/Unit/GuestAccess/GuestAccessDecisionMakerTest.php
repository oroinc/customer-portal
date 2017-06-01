<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\GuestAccess;

use Oro\Bundle\FrontendBundle\GuestAccess\GuestAccessDecisionMaker;
use Oro\Bundle\FrontendBundle\GuestAccess\GuestAccessDecisionMakerInterface;
use Oro\Bundle\FrontendBundle\GuestAccess\Provider\GuestAccessUrlsProviderInterface;
use Oro\Bundle\RedirectBundle\Routing\MatchedUrlDecisionMaker;

class GuestAccessDecisionMakerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @internal
     */
    const SKIPPED_URLS = [
        '/_profiler',
    ];

    /**
     * @internal
     */
    const ALLOWED_URLS = [
        '^/customer/user/login',
        '^/customer/user/registration',
    ];

    /**
     * @internal
     */
    const REDIRECT_URLS = [
        '^/$',
        '^/customer$',
    ];

    /**
     * @var MatchedUrlDecisionMaker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $matchedUrlDecisionMaker;

    /**
     * @var GuestAccessDecisionMaker
     */
    private $guestAccessDecisionMaker;

    /**
     * @var GuestAccessUrlsProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $guestAccessUrlsProvider;

    protected function setUp()
    {
        $this->matchedUrlDecisionMaker = $this->createMock(MatchedUrlDecisionMaker::class);
        $this->guestAccessUrlsProvider = $this->createMock(GuestAccessUrlsProviderInterface::class);
        $this->guestAccessDecisionMaker = new GuestAccessDecisionMaker(
            $this->guestAccessUrlsProvider,
            $this->matchedUrlDecisionMaker
        );
    }

    /**
     * @dataProvider decideDataProvider
     *
     * @param string $url
     * @param int    $expectedDecision
     */
    public function testDecide($url, $expectedDecision)
    {
        $this->guestAccessUrlsProvider
            ->expects(static::any())
            ->method('getAllowedUrls')
            ->willReturn(self::ALLOWED_URLS);

        $this->guestAccessUrlsProvider
            ->expects(static::any())
            ->method('getRedirectUrls')
            ->willReturn(self::REDIRECT_URLS);

        $this->matchedUrlDecisionMaker
            ->expects(static::once())
            ->method('matches')
            ->willReturnMap([
                [self::SKIPPED_URLS[0], false],
                [static::anything(), true],
            ]);

        $decision = $this->guestAccessDecisionMaker->decide($url);

        static::assertSame($expectedDecision, $decision);
    }

    /**
     * @return array
     */
    public function decideDataProvider()
    {
        return [
            'skipped url of MatchedUrlDecisionMaker' => ['/_profiler', GuestAccessDecisionMakerInterface::URL_ALLOW],
            'allowed login url' => ['/customer/user/login', GuestAccessDecisionMakerInterface::URL_ALLOW],
            'allowed registration url' => ['/customer/user/registration', GuestAccessDecisionMakerInterface::URL_ALLOW],
            'allowed inner url' => ['/customer/user/registration/inner', GuestAccessDecisionMakerInterface::URL_ALLOW],
            'disallowed url' => ['/customer/user', GuestAccessDecisionMakerInterface::URL_DISALLOW],
            'disallowed random url' => ['/some-random-url', GuestAccessDecisionMakerInterface::URL_DISALLOW],
            'redirected url' => ['/', GuestAccessDecisionMakerInterface::URL_REDIRECT],
            'another redirected url' => ['/customer', GuestAccessDecisionMakerInterface::URL_REDIRECT],
        ];
    }
}
