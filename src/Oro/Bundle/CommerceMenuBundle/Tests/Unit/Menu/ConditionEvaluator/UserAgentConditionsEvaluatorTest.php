<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Menu\ConditionEvaluator;

use Knp\Menu\ItemInterface;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUserAgentCondition;
use Oro\Bundle\UIBundle\Provider\UserAgentInterface;
use Oro\Bundle\UIBundle\Provider\UserAgentProviderInterface;
use Oro\Bundle\CommerceMenuBundle\Menu\ConditionEvaluator\UserAgentConditionsEvaluator;

class UserAgentConditionsEvaluatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UserAgentProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userAgentProvider;

    /**
     * @var ItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $menuItem;

    /**
     * @var UserAgentConditionsEvaluator
     */
    private $userAgentConditionsEvaluator;

    protected function setUp()
    {
        $this->menuItem = $this->createMock(ItemInterface::class);
        $this->userAgentProvider = $this->createMock(UserAgentProviderInterface::class);
        $this->userAgentConditionsEvaluator = new UserAgentConditionsEvaluator(
            $this->userAgentProvider
        );
    }

    public function testEvaluateWithoutExtras()
    {
        $this->menuItem->expects(static::once())
            ->method('getExtras')
            ->willReturn([]);
        $this->userAgentProvider->expects(static::never())
            ->method('getUserAgent')
            ->willReturn('userAgent');
        static::assertTrue($this->userAgentConditionsEvaluator->evaluate($this->menuItem, []));
    }

    /**
     * @dataProvider getEvaluateDataProvider
     *
     * @param string $operation
     * @param string $value
     * @param bool   $expectedData
     */
    public function testEvaluate($operation, $value, $expectedData)
    {
        $menuUserAgentCondition = $this->createMock(MenuUserAgentCondition::class);
        $menuUserAgentCondition->expects(static::once())
            ->method('getOperation')
            ->willReturn($operation);
        $menuUserAgentCondition->expects(static::once())
            ->method('getValue')
            ->willReturn($value);
        $menuUserAgentCondition->expects(static::once())
            ->method('getConditionGroupIdentifier')
            ->willReturn(1);

        $this->menuItem->expects(static::once())
            ->method('getExtras')
            ->willReturn(['userAgentConditions' => [$menuUserAgentCondition]]);

        $userAgent = $this->createMock(UserAgentInterface::class);
        $userAgent->expects(static::once())
            ->method('getUserAgent')
            ->willReturn('Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
        $this->userAgentProvider->expects(static::once())
            ->method('getUserAgent')
            ->willReturn($userAgent);

        static::assertEquals($expectedData, $this->userAgentConditionsEvaluator->evaluate($this->menuItem, []));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Conditions collection was expected to contain only MenuUserAgentCondition
     */
    public function testExceptionWhenAnotherCollection()
    {
        $this->menuItem->expects(static::once())
            ->method('getExtras')
            ->willReturn(['userAgentConditions' => [new \stdClass]]);
        $userAgent = $this->createMock(UserAgentInterface::class);
        $userAgent->expects(static::once())
            ->method('getUserAgent')
            ->willReturn('test/UserAgent');
        $this->userAgentProvider->expects(static::once())
            ->method('getUserAgent')
            ->willReturn($userAgent);
        $this->userAgentConditionsEvaluator->evaluate($this->menuItem, []);
    }

    /**
     * @return array
     */
    public function getEvaluateDataProvider()
    {
        return [
            'test contains operation' => [
                'operation' => MenuUserAgentCondition::OPERATION_CONTAINS,
                'value' => 'Mozilla',
                'expectedData' => true
            ],
            'test does not contains operation' => [
                'operation' => MenuUserAgentCondition::OPERATION_DOES_NOT_CONTAIN,
                'value' => 'Mozilla',
                'expectedData' => false
            ],
            'test matches operation' => [
                'operation' => MenuUserAgentCondition::OPERATION_MATCHES,
                'value' => 'Chrome|Mozilla',
                'expectedData' => true
            ],
            'test does not matches operation' => [
                'operation' => MenuUserAgentCondition::OPERATION_DOES_NOT_MATCHES,
                'value' => 'Mozilla/(4|5).0',
                'expectedData' => false
            ]
        ];
    }
}
