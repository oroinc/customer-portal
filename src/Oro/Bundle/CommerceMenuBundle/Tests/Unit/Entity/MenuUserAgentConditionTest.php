<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Entity;

use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUserAgentCondition;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class MenuUserAgentConditionTest extends \PHPUnit\Framework\TestCase
{
    use EntityTestCaseTrait;

    public function testGetExtras()
    {
        $properties = [
            ['id', '123'],
            ['menuUpdate', new MenuUpdate()],
            ['conditionGroupIdentifier', 1],
            ['operation', 'does not contain'],
            ['value', 'test'],
        ];

        $entity = new MenuUserAgentCondition();
        self::assertPropertyAccessors($entity, $properties);
    }
}
