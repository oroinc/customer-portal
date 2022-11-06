<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener;

use Knp\Menu\MenuFactory;
use Knp\Menu\MenuItem;
use Oro\Bundle\CustomerBundle\EventListener\NavigationListener;
use Oro\Bundle\NavigationBundle\Event\ConfigureMenuEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class NavigationListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var AuthorizationCheckerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $authorizationChecker;

    /** @var NavigationListener */
    private $listener;

    protected function setUp(): void
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $this->listener = new NavigationListener($this->authorizationChecker);
    }

    /**
     * @dataProvider navigationConfigureDataProvider
     */
    public function testOnNavigationConfigure(
        bool $isGrantedCustomerAddress,
        bool $isGrantedCustomerUserAddress,
        bool $expectedIsDisplayed
    ) {
        $this->authorizationChecker->expects($this->atLeastOnce())
            ->method('isGranted')
            ->willReturnMap([
                ['oro_customer_frontend_customer_address_view', null, $isGrantedCustomerAddress],
                ['oro_customer_frontend_customer_user_address_view', null, $isGrantedCustomerUserAddress]
            ]);

        $factory         = new MenuFactory();
        $menu            = new MenuItem('oro_customer_menu', $factory);
        $addressBookItem = new MenuItem('oro_customer_frontend_customer_user_address_index', $factory);
        $menu->addChild($addressBookItem);

        $eventData = new ConfigureMenuEvent($factory, $menu);
        $this->listener->onNavigationConfigure($eventData);

        $this->assertEquals($expectedIsDisplayed, $addressBookItem->isDisplayed());
    }

    public function navigationConfigureDataProvider(): array
    {
        return [
            'customer granted and customer user granted' => [
                true,
                true,
                true
            ],
            'customer not granted and customer user granted' => [
                false,
                true,
                true
            ],
            'customer granted and customer user not granted' => [
                true,
                false,
                true
            ],
            'customer not granted and customer user not granted' => [
                false,
                false,
                false
            ]
        ];
    }
}
