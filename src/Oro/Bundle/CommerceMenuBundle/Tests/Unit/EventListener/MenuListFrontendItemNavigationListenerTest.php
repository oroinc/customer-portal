<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\EventListener;

use Knp\Menu\MenuFactory;
use Knp\Menu\MenuItem;
use Oro\Bundle\CommerceMenuBundle\EventListener\MenuListFrontendItemNavigationListener;
use Oro\Bundle\NavigationBundle\Event\ConfigureMenuEvent;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MenuListFrontendItemNavigationListenerTest extends TestCase
{
    private AuthorizationCheckerInterface&MockObject $authorizationChecker;
    private TokenAccessorInterface&MockObject $tokenAccessor;
    private MenuListFrontendItemNavigationListener $listener;

    #[\Override]
    protected function setUp(): void
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->listener = new MenuListFrontendItemNavigationListener(
            $this->authorizationChecker,
            $this->tokenAccessor
        );
    }

    public function testOnNavigationConfigureWithoutToken(): void
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

    public function testOnNavigationConfigureWithoutMenuListFrontendItem(): void
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

    public function testOnNavigationConfigureWhenOroConfigSystemIsNotGnanted(): void
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

    public function testOnNavigationConfigureWhenOroNavigationManageMenusIsNotGranted(): void
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

    public function testOnNavigationConfigureWhenAccessIsGranted(): void
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
