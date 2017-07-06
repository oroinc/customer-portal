<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Entity;

use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUserAgentCondition;
use Oro\Bundle\CommerceMenuBundle\Tests\Unit\Entity\Stub\MenuUpdateStub;

use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class MenuUpdateTest extends \PHPUnit_Framework_TestCase
{
    use EntityTestCaseTrait;

    public function testProperties()
    {
        $properties = [
            ['condition', 'condition'],
            ['screens', ['mobile' => ['class' => 'test']]],
        ];

        static::assertPropertyAccessors(new MenuUpdate(), $properties);
    }

    public function testPropertiesCollections()
    {
        $properties = [
            ['menuUserAgentConditions', new MenuUserAgentCondition()],
        ];

        static::assertPropertyCollections(new MenuUpdate(), $properties);
    }

    public function testGetExtras()
    {
        $image = new File();
        $priority = 10;

        $update = new MenuUpdateStub();
        $screens = ['sample-screen'];
        $update
            ->setImage($image)
            ->setScreens($screens)
            ->setCondition('test condition')
            ->setIcon('test-icon')
            ->setPriority($priority)
            ->setDivider(true);

        $expected = [
            'image' => $image,
            'screens' => $screens,
            'condition' => 'test condition',
            'divider' => true,
            'translate_disabled' => false,
            'position' => $priority,
            'icon' => 'test-icon',
        ];

        $this->assertSame($expected, $update->getExtras());
    }
}
