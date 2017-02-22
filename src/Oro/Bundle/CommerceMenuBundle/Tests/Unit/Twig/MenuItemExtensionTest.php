<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Twig;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\MatcherInterface;

use Oro\Bundle\CommerceMenuBundle\Twig\MenuExtension;
use Oro\Component\Testing\Unit\TwigExtensionTestCaseTrait;

class MenuItemExtensionTest extends \PHPUnit_Framework_TestCase
{
    use TwigExtensionTestCaseTrait;

    /** @var MatcherInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $matcher;

    /** @var MenuExtension */
    private $extension;

    public function setUp()
    {
        $this->matcher = $this->getMockBuilder(MatcherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = self::getContainerBuilder()
            ->add('knp_menu.matcher', $this->matcher)
            ->getContainer($this);

        $this->extension = new MenuExtension($container);
    }

    public function testGetName()
    {
        $this->assertEquals(MenuExtension::NAME, $this->extension->getName());
    }

    public function testIsCurrent()
    {
        /** @var ItemInterface|\PHPUnit_Framework_MockObject_MockObject $item */
        $item = $this->createMock(ItemInterface::class);

        $this->matcher->expects($this->once())
            ->method('isCurrent')
            ->with(self::identicalTo($item))
            ->will($this->returnValue(true));

        $this->assertTrue(
            self::callTwigFunction($this->extension, 'oro_commercemenu_is_current', [$item])
        );
    }

    public function testIsAncestor()
    {
        /** @var ItemInterface|\PHPUnit_Framework_MockObject_MockObject $item */
        $item = $this->createMock(ItemInterface::class);

        $this->matcher
            ->expects($this->once())
            ->method('isAncestor')
            ->with(self::identicalTo($item))
            ->will($this->returnValue(true));

        $this->assertTrue(
            self::callTwigFunction($this->extension, 'oro_commercemenu_is_ancestor', [$item])
        );
    }
}
