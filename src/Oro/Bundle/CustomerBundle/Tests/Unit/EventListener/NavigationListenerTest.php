<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener;

use Knp\Menu\MenuItem;
use Knp\Menu\MenuFactory;

use Oro\Bundle\CustomerBundle\EventListener\NavigationListener;
use Oro\Bundle\NavigationBundle\Event\ConfigureMenuEvent;
use Oro\Bundle\SecurityBundle\SecurityFacade;

class NavigationListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var SecurityFacade|\PHPUnit_Framework_MockObject_MockObject */
    private $securityFacade;

    /** @var NavigationListener */
    private $listener;

    protected function setUp()
    {
        $this->securityFacade = $this->createMock(SecurityFacade::class);
        $this->listener       = new NavigationListener($this->securityFacade);
    }

    /**
     * @param bool $isGrantedCustomer
     * @param bool $isGrantedCustomerUser
     * @param bool $expectedIsDisplayed
     *
     * @dataProvider navigationConfigureDataProvider
     */
    public function testOnNavigationConfigure($isGrantedCustomer, $isGrantedCustomerUser, $expectedIsDisplayed)
    {
        $this->securityFacade
            ->expects($this->atLeastOnce())
            ->method('isGranted')
            ->will($this->returnValueMap(
                [
                    ['oro_customer_frontend_customer_view', null, $isGrantedCustomer],
                    ['oro_customer_frontend_customer_user_view', null, $isGrantedCustomerUser]
                ]
            ));

        $factory         = new MenuFactory();
        $menu            = new MenuItem('oro_customer_menu', $factory);
        $addressBookItem = new MenuItem('oro_customer_frontend_customer_user_address_index', $factory);
        $menu->addChild($addressBookItem);

        $eventData = new ConfigureMenuEvent($factory, $menu);
        $this->listener->onNavigationConfigure($eventData);

        $this->assertEquals($expectedIsDisplayed, $addressBookItem->isDisplayed());
    }

    /**
     * @return array
     */
    public function navigationConfigureDataProvider()
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
