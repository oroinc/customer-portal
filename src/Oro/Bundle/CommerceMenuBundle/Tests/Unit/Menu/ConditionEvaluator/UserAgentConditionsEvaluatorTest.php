<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Menu\ConditionEvaluator;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\PersistentCollection;
use Knp\Menu\ItemInterface;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUserAgentCondition;
use Oro\Bundle\CommerceMenuBundle\Menu\ConditionEvaluator\UserAgentConditionsEvaluator;
use Oro\Bundle\UIBundle\Provider\UserAgentInterface;
use Oro\Bundle\UIBundle\Provider\UserAgentProviderInterface;

class UserAgentConditionsEvaluatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var UserAgentProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $userAgentProvider;

    /**
     * @var ItemInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $menuItem;

    /**
     * @var EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $entityManager;

    /**
     * @var ClassMetadata|\PHPUnit\Framework\MockObject\MockObject
     */
    private $metadata;

    /**
     * @var UserAgentConditionsEvaluator
     */
    private $userAgentConditionsEvaluator;

    protected function setUp()
    {
        $this->menuItem = $this->createMock(ItemInterface::class);
        $this->userAgentProvider = $this->createMock(UserAgentProviderInterface::class);
        $this->entityManager = $this->createMock(EntityManager::class);
        $this->metadata = $this->createMock(ClassMetadata::class);
        $this->userAgentConditionsEvaluator = new UserAgentConditionsEvaluator(
            $this->userAgentProvider
        );
    }

    public function testEvaluateWithoutExtras()
    {
        $this->menuItem->expects(static::once())
            ->method('getExtra')
            ->with(UserAgentConditionsEvaluator::MENU_CONDITION_KEY_EXTRA)
            ->willReturn(null);
        $this->userAgentProvider->expects(static::never())
            ->method('getUserAgent')
            ->willReturn('userAgent');
        static::assertTrue($this->userAgentConditionsEvaluator->evaluate($this->menuItem, []));
    }

    public function testEvaluateWithEmptyExtra()
    {
        $collection = new PersistentCollection(
            $this->entityManager,
            $this->metadata,
            new ArrayCollection([])
        );
        $this->menuItem->expects(static::once())
            ->method('getExtra')
            ->with(UserAgentConditionsEvaluator::MENU_CONDITION_KEY_EXTRA)
            ->willReturn($collection);
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

        $collection = new PersistentCollection(
            $this->entityManager,
            $this->metadata,
            new ArrayCollection([$menuUserAgentCondition])
        );

        $this->menuItem->expects(static::once())
            ->method('getExtra')
            ->with(UserAgentConditionsEvaluator::MENU_CONDITION_KEY_EXTRA)
            ->willReturn($collection);

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
        $collection = new PersistentCollection(
            $this->entityManager,
            $this->metadata,
            new ArrayCollection([new \stdClass])
        );

        $this->menuItem->expects(static::once())
            ->method('getExtra')
            ->with(UserAgentConditionsEvaluator::MENU_CONDITION_KEY_EXTRA)
            ->willReturn($collection);

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
