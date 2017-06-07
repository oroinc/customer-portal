<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\GuestAccess;

use Oro\Bundle\FrontendBundle\GuestAccess\GuestAccessDecisionMaker;
use Oro\Bundle\FrontendBundle\GuestAccess\GuestAccessDecisionMakerInterface;
use Oro\Bundle\FrontendBundle\GuestAccess\Provider\GuestAccessAllowedUrlsProviderInterface;
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
        '^/customer/user/login$',
        '^/customer/user/registration$',
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
     * @var GuestAccessAllowedUrlsProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $guestAccessAllowedUrlsProvider;

    protected function setUp()
    {
        $this->matchedUrlDecisionMaker = $this->createMock(MatchedUrlDecisionMaker::class);
        $this->guestAccessAllowedUrlsProvider = $this->createMock(GuestAccessAllowedUrlsProviderInterface::class);
        $this->guestAccessDecisionMaker = new GuestAccessDecisionMaker(
            $this->guestAccessAllowedUrlsProvider,
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
        $this->guestAccessAllowedUrlsProvider
            ->expects(static::any())
            ->method('getAllowedUrlsPatterns')
            ->willReturn(self::ALLOWED_URLS);

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
            'disallowed inner url' => ['/customer/user/login/inner', GuestAccessDecisionMakerInterface::URL_DISALLOW],
            'disallowed parent url' => ['/customer/user', GuestAccessDecisionMakerInterface::URL_DISALLOW],
            'disallowed random url' => ['/some-random-url', GuestAccessDecisionMakerInterface::URL_DISALLOW],
        ];
    }
}
