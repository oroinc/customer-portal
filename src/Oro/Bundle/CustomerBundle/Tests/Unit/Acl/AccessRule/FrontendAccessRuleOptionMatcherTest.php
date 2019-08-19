<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Acl\AccessRule;

use Oro\Bundle\CustomerBundle\Acl\AccessRule\FrontendAccessRuleOptionMatcher;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\SecurityBundle\AccessRule\AccessRuleOptionMatcherInterface;
use Oro\Bundle\SecurityBundle\AccessRule\Criteria;

class FrontendAccessRuleOptionMatcherTest extends \PHPUnit\Framework\TestCase
{
    /** @var AccessRuleOptionMatcherInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $innerMatcher;

    /** @var FrontendHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $frontendHelper;

    /** @var FrontendAccessRuleOptionMatcher */
    private $frontendMatcher;

    protected function setUp()
    {
        $this->innerMatcher = $this->createMock(AccessRuleOptionMatcherInterface::class);
        $this->frontendHelper = $this->createMock(FrontendHelper::class);

        $this->frontendMatcher = new FrontendAccessRuleOptionMatcher(
            $this->innerMatcher,
            $this->frontendHelper
        );
    }

    public function testNotFrontendOption()
    {
        $criteria = $this->createMock(Criteria::class);
        $optionName = 'test_name';
        $optionValue = 'test_value';
        $result = true;

        $this->innerMatcher->expects(self::once())
            ->method('matches')
            ->with(self::identicalTo($criteria), $optionName, $optionValue)
            ->willReturn($result);

        self::assertSame(
            $result,
            $this->frontendMatcher->matches($criteria, $optionName, $optionValue)
        );
    }

    public function testFrontendOptionEqualsToTrueAndFrontendContext()
    {
        $criteria = $this->createMock(Criteria::class);

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);
        $this->innerMatcher->expects(self::never())
            ->method('matches');

        self::assertTrue(
            $this->frontendMatcher->matches($criteria, 'frontend', true)
        );
    }

    public function testFrontendOptionEqualsToTrueAndNotFrontendContext()
    {
        $criteria = $this->createMock(Criteria::class);

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);
        $this->innerMatcher->expects(self::never())
            ->method('matches');

        self::assertFalse(
            $this->frontendMatcher->matches($criteria, 'frontend', true)
        );
    }

    public function testFrontendOptionEqualsToFalseAndFrontendContext()
    {
        $criteria = $this->createMock(Criteria::class);

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);
        $this->innerMatcher->expects(self::never())
            ->method('matches');

        self::assertFalse(
            $this->frontendMatcher->matches($criteria, 'frontend', false)
        );
    }

    public function testFrontendOptionEqualsToFalseAndNotFrontendContext()
    {
        $criteria = $this->createMock(Criteria::class);

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);
        $this->innerMatcher->expects(self::never())
            ->method('matches');

        self::assertTrue(
            $this->frontendMatcher->matches($criteria, 'frontend', false)
        );
    }
}
