<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Menu\ConditionEvaluator;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\PersistentCollection;
use Knp\Menu\ItemInterface;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUserAgentCondition;
use Oro\Bundle\CommerceMenuBundle\Menu\ConditionEvaluator\UserAgentConditionsEvaluator;
use Oro\Bundle\UIBundle\Provider\UserAgentInterface;
use Oro\Bundle\UIBundle\Provider\UserAgentProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UserAgentConditionsEvaluatorTest extends TestCase
{
    private UserAgentProviderInterface&MockObject $userAgentProvider;
    private ItemInterface&MockObject $menuItem;
    private EntityManagerInterface&MockObject $entityManager;
    private ClassMetadata&MockObject $metadata;
    private UserAgentConditionsEvaluator $userAgentConditionsEvaluator;

    #[\Override]
    protected function setUp(): void
    {
        $this->menuItem = $this->createMock(ItemInterface::class);
        $this->userAgentProvider = $this->createMock(UserAgentProviderInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->metadata = $this->createMock(ClassMetadata::class);

        $this->userAgentConditionsEvaluator = new UserAgentConditionsEvaluator(
            $this->userAgentProvider
        );
    }

    public function testEvaluateWithoutExtras(): void
    {
        $this->menuItem->expects(self::once())
            ->method('getExtra')
            ->with(UserAgentConditionsEvaluator::MENU_CONDITION_KEY_EXTRA)
            ->willReturn(null);
        $this->userAgentProvider->expects(self::never())
            ->method('getUserAgent')
            ->willReturn('userAgent');
        self::assertTrue($this->userAgentConditionsEvaluator->evaluate($this->menuItem, []));
    }

    public function testEvaluateWithEmptyExtra(): void
    {
        $collection = new PersistentCollection(
            $this->entityManager,
            $this->metadata,
            new ArrayCollection([])
        );
        $this->menuItem->expects(self::once())
            ->method('getExtra')
            ->with(UserAgentConditionsEvaluator::MENU_CONDITION_KEY_EXTRA)
            ->willReturn($collection);
        self::assertTrue($this->userAgentConditionsEvaluator->evaluate($this->menuItem, []));
    }

    /**
     * @dataProvider getEvaluateDataProvider
     */
    public function testEvaluate(string $operation, string $value, bool $expectedData): void
    {
        $menuUserAgentCondition = $this->createMock(MenuUserAgentCondition::class);
        $menuUserAgentCondition->expects(self::once())
            ->method('getOperation')
            ->willReturn($operation);
        $menuUserAgentCondition->expects(self::once())
            ->method('getValue')
            ->willReturn($value);
        $menuUserAgentCondition->expects(self::once())
            ->method('getConditionGroupIdentifier')
            ->willReturn(1);

        $collection = new PersistentCollection(
            $this->entityManager,
            $this->metadata,
            new ArrayCollection([$menuUserAgentCondition])
        );

        $this->menuItem->expects(self::once())
            ->method('getExtra')
            ->with(UserAgentConditionsEvaluator::MENU_CONDITION_KEY_EXTRA)
            ->willReturn($collection);

        $userAgent = $this->createMock(UserAgentInterface::class);
        $userAgent->expects(self::once())
            ->method('getUserAgent')
            ->willReturn('Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
        $this->userAgentProvider->expects(self::once())
            ->method('getUserAgent')
            ->willReturn($userAgent);

        self::assertEquals($expectedData, $this->userAgentConditionsEvaluator->evaluate($this->menuItem, []));
    }

    public function testExceptionWhenAnotherCollection(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Conditions collection was expected to contain only MenuUserAgentCondition');

        $collection = new PersistentCollection(
            $this->entityManager,
            $this->metadata,
            new ArrayCollection([new \stdClass()])
        );

        $this->menuItem->expects(self::once())
            ->method('getExtra')
            ->with(UserAgentConditionsEvaluator::MENU_CONDITION_KEY_EXTRA)
            ->willReturn($collection);

        $userAgent = $this->createMock(UserAgentInterface::class);
        $userAgent->expects(self::once())
            ->method('getUserAgent')
            ->willReturn('test/UserAgent');
        $this->userAgentProvider->expects(self::once())
            ->method('getUserAgent')
            ->willReturn($userAgent);
        $this->userAgentConditionsEvaluator->evaluate($this->menuItem, []);
    }

    public function getEvaluateDataProvider(): array
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
