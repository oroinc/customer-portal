<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUserAgentCondition;
use Oro\Bundle\CommerceMenuBundle\Form\DataTransformer\MenuUserAgentConditionsCollectionTransformer;
use PHPUnit\Framework\TestCase;

class MenuUserAgentConditionsCollectionTransformerTest extends TestCase
{
    private MenuUserAgentConditionsCollectionTransformer $transformer;

    #[\Override]
    protected function setUp(): void
    {
        $this->transformer = new MenuUserAgentConditionsCollectionTransformer();
    }

    /**
     * @dataProvider transformDataProvider
     */
    public function testTransform(mixed $menuUserAgentConditionsCollection, array $expectedGroupedConditionsArray): void
    {
        $groupedConditionsArray = $this->transformer->transform($menuUserAgentConditionsCollection);

        self::assertSame($expectedGroupedConditionsArray, $groupedConditionsArray);
    }

    public function transformDataProvider(): array
    {
        $menuUserAgentConditionsArray = [
            $this->getMenuUserAgentCondition(1),
            $this->getMenuUserAgentCondition(1),
            $this->getMenuUserAgentCondition(0),
        ];

        return [
            'array'  => [
                'menuUserAgentConditionsCollection' => ['key' => 'value'],
                'expectedGroupedConditionsArray' => ['key' => 'value'],
            ],
            'not collection' => [
                'menuUserAgentConditionsCollection' => 'not a collection',
                'expectedGroupedConditionsArray' => [],
            ],
            'empty collection' => [
                'menuUserAgentConditionsCollection' => new ArrayCollection(),
                'expectedGroupedConditionsArray' => [],
            ],
            'normal collection' => [
                'menuUserAgentConditionsCollection' => new ArrayCollection($menuUserAgentConditionsArray),
                'expectedGroupedConditionsArray' => [
                    0 => [$menuUserAgentConditionsArray[2]],
                    1 => [$menuUserAgentConditionsArray[0], $menuUserAgentConditionsArray[1]],
                ],
            ],
        ];
    }

    public function testTransformWithInvalidCollection(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Conditions collection was expected to contain only MenuUserAgentCondition');

        $menuUserAgentConditionsCollection = new ArrayCollection(['not a MenuUserAgentCondition']);
        $this->transformer->transform($menuUserAgentConditionsCollection);
    }

    /**
     * @dataProvider reverseTransformDataProvider
     */
    public function testReverseTransform(
        mixed $groupedConditionsArray,
        array $expectedMenuUserAgentConditions
    ): void {
        $menuUserAgentConditionsCollection = $this->transformer->reverseTransform($groupedConditionsArray);

        self::assertSame($expectedMenuUserAgentConditions, $menuUserAgentConditionsCollection->toArray());
    }

    public function reverseTransformDataProvider(): array
    {
        $groupedConditionsArray = [
            0 => [
                $this->getMenuUserAgentCondition(0),
            ],
            1 => [
                $this->getMenuUserAgentCondition(1),
                $this->getMenuUserAgentCondition(1),
            ],
        ];

        return [
            'not array' => [
                'groupedConditionsArray' => 'not array',
                'expectedMenuUserAgentConditions' => [],
            ],
            'empty array' => [
                'groupedConditionsArray' => [],
                'expectedMenuUserAgentConditions' => [],
            ],
            'group is not an array' => [
                'groupedConditionsArray' => [0 => 'not array'],
                'expectedMenuUserAgentConditions' => [],
            ],
            'normal array' => [
                'groupedConditionsArray' => $groupedConditionsArray,
                'expectedMenuUserAgentConditions' => [
                    $groupedConditionsArray[0][0],
                    $groupedConditionsArray[1][0],
                    $groupedConditionsArray[1][1],
                ],
            ],
        ];
    }

    public function testReverseTransformWithInvalidConditionsGroup(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Conditions group was expected to contain only MenuUserAgentCondition');

        $groupedConditionsArray = [0 => ['random string']];
        $this->transformer->reverseTransform($groupedConditionsArray);
    }

    private function getMenuUserAgentCondition(int $conditionGroupIdentifier): MenuUserAgentCondition
    {
        $menuUserAgentCondition = $this->createMock(MenuUserAgentCondition::class);
        $menuUserAgentCondition->expects(self::any())
            ->method('getConditionGroupIdentifier')
            ->willReturn($conditionGroupIdentifier);

        $menuUserAgentCondition->expects(self::any())
            ->method('setConditionGroupIdentifier')
            ->with($conditionGroupIdentifier);

        return $menuUserAgentCondition;
    }
}
