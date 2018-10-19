<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\GuestAccess;

use Oro\Bundle\FrontendBundle\GuestAccess\GuestAccessDecisionMaker;
use Oro\Bundle\FrontendBundle\GuestAccess\GuestAccessDecisionMakerInterface;
use Oro\Bundle\FrontendBundle\GuestAccess\Provider\GuestAccessAllowedUrlsProviderInterface;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;

class GuestAccessDecisionMakerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @internal
     */
    const ALLOWED_URLS = [
        '^/customer/user/login$',
        '^/customer/user/registration$',
    ];

    /**
     * @internal
     */
    const NOT_FRONTEND_URL = '/admin/';

    /**
     * @var FrontendHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $frontendHelper;

    /**
     * @var GuestAccessAllowedUrlsProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $guestAccessAllowedUrlsProvider;

    protected function setUp()
    {
        $this->frontendHelper = $this->createMock(FrontendHelper::class);
        $this->guestAccessAllowedUrlsProvider = $this->createMock(GuestAccessAllowedUrlsProviderInterface::class);
    }

    /**
     * @dataProvider decideDataProvider
     *
     * @param string $url
     * @param int    $expectedDecision
     */
    public function testDecide($url, $expectedDecision)
    {
        $installed = true;
        $this->guestAccessAllowedUrlsProvider
            ->expects(static::any())
            ->method('getAllowedUrlsPatterns')
            ->willReturn(self::ALLOWED_URLS);

        $this->frontendHelper
            ->expects(static::once())
            ->method('isFrontendUrl')
            ->willReturnMap([
                [self::NOT_FRONTEND_URL, false],
                [static::anything(), true],
            ]);

        $decision = $this->getGuestAccessDecisionMaker($installed)->decide($url);

        static::assertSame($expectedDecision, $decision);
    }

    /**
     * @return array
     */
    public function decideDataProvider()
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

    public function testDecideWhenNotInstalled()
    {
        $installed = false;
        $this->frontendHelper
            ->expects(static::never())
            ->method('isFrontendUrl');

        $decision = $this->getGuestAccessDecisionMaker($installed)->decide('/random-url');

        static::assertSame(GuestAccessDecisionMakerInterface::URL_ALLOW, $decision);
    }

    /**
     * @param $installed
     *
     * @return GuestAccessDecisionMaker
     */
    protected function getGuestAccessDecisionMaker($installed)
    {
        return new GuestAccessDecisionMaker(
            $this->guestAccessAllowedUrlsProvider,
            $this->frontendHelper,
            $installed
        );
    }
}
