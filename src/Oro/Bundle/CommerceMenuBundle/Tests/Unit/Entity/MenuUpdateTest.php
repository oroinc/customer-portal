<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Entity;

use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUserAgentCondition;
use Oro\Bundle\CommerceMenuBundle\Tests\Unit\Entity\Stub\MenuUpdateStub;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class MenuUpdateTest extends \PHPUnit\Framework\TestCase
{
    use EntityTestCaseTrait;

    public function testProperties(): void
    {
        $properties = [
            ['condition', 'condition'],
            ['screens', ['mobile' => ['class' => 'test']]],
            ['contentNode', new ContentNode(), false],
            ['systemPageRoute', 'sampleRoute', false],
            ['linkTarget', 0, 1],
            ['menuTemplate', 'list', false],
            ['maxTraverseLevel', 2, false],
        ];

        self::assertPropertyAccessors(new MenuUpdate(), $properties);
    }

    public function testPropertiesCollections(): void
    {
        $properties = [
            ['menuUserAgentConditions', new MenuUserAgentCondition()],
        ];

        self::assertPropertyCollections(new MenuUpdate(), $properties);
    }

    public function testGetLinkAttributes(): void
    {
        $update = new MenuUpdateStub();
        self::assertSame([], $update->getLinkAttributes());
        $update->setLinkTarget(0);
        self::assertSame(['target' => '_blank'], $update->getLinkAttributes());
    }
}
