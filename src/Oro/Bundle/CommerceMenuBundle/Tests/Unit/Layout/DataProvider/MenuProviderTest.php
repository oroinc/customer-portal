<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Layout\DataProvider;

use Knp\Menu\ItemInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Oro\Bundle\CommerceMenuBundle\Layout\DataProvider\MenuProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MenuProviderTest extends TestCase
{
    private MenuProviderInterface&MockObject $builderChainProvider;
    private MenuProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->builderChainProvider = $this->createMock(MenuProviderInterface::class);
        $this->provider = new MenuProvider($this->builderChainProvider);
    }

    public function testGetMenu(): void
    {
        $menuName = 'menuName';
        $options = ['option1', 'option2'];

        $item = $this->createMock(ItemInterface::class);

        $this->builderChainProvider->expects($this->once())
            ->method('get')
            ->with($menuName, $options)
            ->willReturn($item);

        $this->assertSame($item, $this->provider->getMenu($menuName, $options));
    }

    public function testGetMenuWithDefaultOptions(): void
    {
        $menuName = 'menuName';
        $options = ['check_access_not_logged_in' => true];

        $item = $this->createMock(ItemInterface::class);

        $this->builderChainProvider->expects($this->once())
            ->method('get')
            ->with($menuName, $options)
            ->willReturn($item);

        $this->assertSame($item, $this->provider->getMenu($menuName));
    }
}
