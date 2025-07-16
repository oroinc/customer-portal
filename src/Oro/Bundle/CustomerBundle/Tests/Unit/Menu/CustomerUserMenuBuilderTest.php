<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Menu;

use Knp\Menu\ItemInterface;
use Oro\Bundle\CustomerBundle\Menu\CustomerUserMenuBuilder;
use PHPUnit\Framework\TestCase;

class CustomerUserMenuBuilderTest extends TestCase
{
    private CustomerUserMenuBuilder $builder;

    #[\Override]
    protected function setUp(): void
    {
        $this->builder = new CustomerUserMenuBuilder();
    }

    public function testBuild(): void
    {
        $child = $this->createMock(ItemInterface::class);
        $child->expects($this->once())
            ->method('setLabel')
            ->with('')
            ->willReturnSelf();
        $child->expects($this->once())
            ->method('setExtra')
            ->with('divider', true)
            ->willReturnSelf();

        $menu = $this->createMock(ItemInterface::class);
        $menu->expects($this->once())
            ->method('setExtra')
            ->with('type', 'dropdown');
        $menu->expects($this->exactly(2))
            ->method('addChild')
            ->withConsecutive(
                ['divider-customer-user-before-logout'],
                [
                    'oro.customer.menu.customer_user_logout.label',
                    [
                        'route' => 'oro_customer_customer_user_security_logout',
                        'linkAttributes' => ['class' => 'no-hash']
                    ]
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $child,
                $this->createMock(ItemInterface::class)
            );

        $this->builder->build($menu);
    }
}
