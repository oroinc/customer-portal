<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\EventListener;

use Knp\Menu\MenuFactory;
use Knp\Menu\MenuItem;
use Oro\Bundle\CommerceMenuBundle\EventListener\MenuListFrontendItemNavigationListener;
use Oro\Bundle\NavigationBundle\Event\ConfigureMenuEvent;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MenuListFrontendItemNavigationListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var AuthorizationCheckerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $authorizationChecker;

    /** @var TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenAccessor;

    /** @var MenuListFrontendItemNavigationListener */
    private $listener;

    protected function setUp(): void
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->listener = new MenuListFrontendItemNavigationListener(
            $this->authorizationChecker,
            $this->tokenAccessor
        );
    }

    public function testOnNavigationConfigureWithoutToken()
    {
        $this->tokenAccessor->expects($this->once())
            ->method('hasUser')
            ->willReturn(false);

        $this->authorizationChecker->expects($this->never())
            ->method('isGranted');

        $factory = new MenuFactory();
        $menu = new MenuItem('parent_item', $factory);
        $menuListFrontendItem = new MenuItem('menu_list_frontend', $factory);
        $menu->addChild($menuListFrontendItem);

        $this->listener->onNavigationConfigure(new ConfigureMenuEvent($factory, $menu));

        $this->assertTrue($menuListFrontendItem->isDisplayed());
    }

    public function testOnNavigationConfigureWithoutMenuListFrontendItem()
    {
        $this->tokenAccessor->expects($this->once())
            ->method('hasUser')
            ->willReturn(true);

        $this->authorizationChecker->expects($this->never())
            ->method('isGranted');

        $factory = new MenuFactory();
        $menu = new MenuItem('parent_item', $factory);

        $this->listener->onNavigationConfigure(new ConfigureMenuEvent($factory, $menu));
    }

    public function testOnNavigationConfigureWhenOroConfigSystemIsNotGnanted()
    {
        $this->tokenAccessor->expects($this->once())
            ->method('hasUser')
            ->willReturn(true);

        $this->authorizationChecker->expects($this->any())
            ->method('isGranted')
            ->willReturnMap([
                ['oro_config_system', null, false],
                ['oro_navigation_manage_menus', null, true]
            ]);

        $factory = new MenuFactory();
        $menu = new MenuItem('parent_item', $factory);
        $menuListFrontendItem = new MenuItem('menu_list_frontend', $factory);
        $menu->addChild($menuListFrontendItem);

        $this->listener->onNavigationConfigure(new ConfigureMenuEvent($factory, $menu));

        $this->assertFalse($menuListFrontendItem->isDisplayed());
    }

    public function testOnNavigationConfigureWhenOroNavigationManageMenusIsNotGranted()
    {
        $this->tokenAccessor->expects($this->once())
            ->method('hasUser')
            ->willReturn(true);

        $this->authorizationChecker->expects($this->any())
            ->method('isGranted')
            ->willReturnMap([
                ['oro_config_system', null, true],
                ['oro_navigation_manage_menus', null, false]
            ]);

        $factory = new MenuFactory();
        $menu = new MenuItem('parent_item', $factory);
        $menuListFrontendItem = new MenuItem('menu_list_frontend', $factory);
        $menu->addChild($menuListFrontendItem);

        $this->listener->onNavigationConfigure(new ConfigureMenuEvent($factory, $menu));

        $this->assertFalse($menuListFrontendItem->isDisplayed());
    }

    public function testOnNavigationConfigureWhenAccessIsGranted()
    {
        $this->tokenAccessor->expects($this->once())
            ->method('hasUser')
            ->willReturn(true);

        $this->authorizationChecker->expects($this->any())
            ->method('isGranted')
            ->willReturnMap([
                ['oro_config_system', null, true],
                ['oro_navigation_manage_menus', null, true]
            ]);

        $factory = new MenuFactory();
        $menu = new MenuItem('parent_item', $factory);
        $menuListFrontendItem = new MenuItem('menu_list_frontend', $factory);
        $menu->addChild($menuListFrontendItem);

        $this->listener->onNavigationConfigure(new ConfigureMenuEvent($factory, $menu));

        $this->assertTrue($menuListFrontendItem->isDisplayed());
    }
}
