<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUserAgentCondition;
use Oro\Bundle\CommerceMenuBundle\Form\DataTransformer\MenuUserAgentConditionsCollectionTransformer;

class MenuUserAgentConditionsCollectionTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MenuUserAgentConditionsCollectionTransformer
     */
    private $transformer;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
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

        static::assertSame($expectedGroupedConditionsArray, $groupedConditionsArray);
    }

    /**
     * @return array
     */
    public function testTransformDataProvider()
    {
        $menuUserAgentConditionsArray = [
            $this->mockMenuUserAgentCondition(1),
            $this->mockMenuUserAgentCondition(2),
            $this->mockMenuUserAgentCondition(2),
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
                    1 => [$menuUserAgentConditionsArray[0]],
                    2 => [$menuUserAgentConditionsArray[1], $menuUserAgentConditionsArray[2]],
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

        static::assertSame($expectedMenuUserAgentConditions, $menuUserAgentConditionsCollection->toArray());
    }

    /**
     * @return array
     */
    public function testReverseTransformDataProvider()
    {
        $groupedConditionsArray = [
            1 => [
                $this->mockMenuUserAgentCondition(1),
            ],
            2 => [
                $this->mockMenuUserAgentCondition(2),
                $this->mockMenuUserAgentCondition(2),
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
                'groupedConditionsArray' => [1 => 'not array'],
                'expectedMenuUserAgentConditions' => [],
            ],
            'normal array' => [
                'groupedConditionsArray' => $groupedConditionsArray,
                'expectedMenuUserAgentConditions' => [
                    $groupedConditionsArray[1][0],
                    $groupedConditionsArray[2][0],
                    $groupedConditionsArray[2][1],
                ],
            ],
        ];
    }

    public function testReverseTransformWithInvalidConditionsGroup()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Conditions group was expected to contain only MenuUserAgentCondition');

        $groupedConditionsArray = [1 => ['random string']];
        $this->transformer->reverseTransform($groupedConditionsArray);
    }

    /**
     * @param int $conditionGroupIdentifier
     *
     * @return MenuUserAgentCondition|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockMenuUserAgentCondition($conditionGroupIdentifier)
    {
        $menuUserAgentCondition = $this->createMock(MenuUserAgentCondition::class);
        $menuUserAgentCondition
            ->expects(static::any())
            ->method('getConditionGroupIdentifier')
            ->willReturn($conditionGroupIdentifier);

        $menuUserAgentCondition
            ->expects(static::any())
            ->method('setConditionGroupIdentifier')
            ->with($conditionGroupIdentifier);

        return $menuUserAgentCondition;
    }
}
