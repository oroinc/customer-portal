<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUserAgentCondition;
use Oro\Bundle\CommerceMenuBundle\Form\DataTransformer\MenuUserAgentConditionsCollectionTransformer;

class MenuUserAgentConditionsCollectionTransformerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MenuUserAgentConditionsCollectionTransformer
     */
    private $transformer;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->transformer = new MenuUserAgentConditionsCollectionTransformer();
    }

    /**
     * @dataProvider testTransformDataProvider
     *
     * @param mixed $menuUserAgentConditionsCollection
     * @param array $expectedGroupedConditionsArray
     */
    public function testTransform($menuUserAgentConditionsCollection, array $expectedGroupedConditionsArray)
    {
        $groupedConditionsArray = $this->transformer->transform($menuUserAgentConditionsCollection);

        self::assertSame($expectedGroupedConditionsArray, $groupedConditionsArray);
    }

    /**
     * @return array
     */
    public function testTransformDataProvider()
    {
        $menuUserAgentConditionsArray = [
            $this->mockMenuUserAgentCondition(1),
            $this->mockMenuUserAgentCondition(1),
            $this->mockMenuUserAgentCondition(0),
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

    public function testTransformWithInvalidCollection()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Conditions collection was expected to contain only MenuUserAgentCondition');

        $menuUserAgentConditionsCollection = new ArrayCollection(['not a MenuUserAgentCondition']);
        $this->transformer->transform($menuUserAgentConditionsCollection);
    }

    /**
     * @dataProvider testReverseTransformDataProvider
     *
     * @param mixed $groupedConditionsArray
     * @param array $expectedMenuUserAgentConditions
     */
    public function testReverseTransform(
        $groupedConditionsArray,
        array $expectedMenuUserAgentConditions
    ) {
        $menuUserAgentConditionsCollection = $this->transformer->reverseTransform($groupedConditionsArray);

        self::assertSame($expectedMenuUserAgentConditions, $menuUserAgentConditionsCollection->toArray());
    }

    /**
     * @return array
     */
    public function testReverseTransformDataProvider()
    {
        $groupedConditionsArray = [
            0 => [
                $this->mockMenuUserAgentCondition(0),
            ],
            1 => [
                $this->mockMenuUserAgentCondition(1),
                $this->mockMenuUserAgentCondition(1),
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

    public function testReverseTransformWithInvalidConditionsGroup()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Conditions group was expected to contain only MenuUserAgentCondition');

        $groupedConditionsArray = [0 => ['random string']];
        $this->transformer->reverseTransform($groupedConditionsArray);
    }

    /**
     * @param int $conditionGroupIdentifier
     *
     * @return MenuUserAgentCondition|\PHPUnit\Framework\MockObject\MockObject
     */
    private function mockMenuUserAgentCondition($conditionGroupIdentifier)
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
