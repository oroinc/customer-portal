<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener;

use Knp\Menu\MenuFactory;
use Knp\Menu\MenuItem;
use Oro\Bundle\CustomerBundle\EventListener\NavigationListener;
use Oro\Bundle\NavigationBundle\Event\ConfigureMenuEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class NavigationListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var AuthorizationCheckerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $authorizationChecker;

    /** @var NavigationListener */
    private $listener;

    protected function setUp()
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $this->listener = new NavigationListener($this->authorizationChecker);
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
        $this->authorizationChecker->expects($this->atLeastOnce())
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
