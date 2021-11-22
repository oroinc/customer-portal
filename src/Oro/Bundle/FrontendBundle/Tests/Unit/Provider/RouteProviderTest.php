<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Provider;

use Oro\Bundle\ActionBundle\Provider\RouteProvider as DefaultRouteProvider;
use Oro\Bundle\FrontendBundle\Provider\RouteProvider;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;

class RouteProviderTest extends \PHPUnit\Framework\TestCase
{
    private const DEFAULT_ROUTES = [
        'dialog'    => 'oro_action_widget_form',
        'page'      => 'oro_action_widget_form_page',
        'execution' => 'oro_action_operation_execute',
        'widget'    => 'oro_action_widget_buttons'
    ];

    private const FRONTEND_ROUTES = [
        'dialog'    => 'oro_frontend_action_widget_form',
        'page'      => 'oro_frontend_action_widget_form_page',
        'execution' => 'oro_frontend_action_operation_execute',
        'widget'    => 'oro_frontend_action_widget_buttons'
    ];

    /** @var \PHPUnit\Framework\MockObject\MockObject|FrontendHelper */
    private $frontendHelper;

    /** @var RouteProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->frontendHelper = $this->createMock(FrontendHelper::class);

        $this->provider = new RouteProvider(
            new DefaultRouteProvider(
                self::DEFAULT_ROUTES['dialog'],
                self::DEFAULT_ROUTES['page'],
                self::DEFAULT_ROUTES['execution'],
                self::DEFAULT_ROUTES['widget']
            ),
            $this->frontendHelper,
            self::FRONTEND_ROUTES['dialog'],
            self::FRONTEND_ROUTES['page'],
            self::FRONTEND_ROUTES['execution'],
            self::FRONTEND_ROUTES['widget']
        );
    }

    /**
     * @dataProvider applicationRoutesProvider
     */
    public function testGetWidgetRoute(bool $isFrontendRequest, array $expectedRoutes = [])
    {
        $this->frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn($isFrontendRequest);

        $this->assertEquals($expectedRoutes['widget'], $this->provider->getWidgetRoute());
    }

    /**
     * @dataProvider applicationRoutesProvider
     */
    public function testGetDialogRoute(bool $isFrontendRequest, array $expectedRoutes = [])
    {
        $this->frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn($isFrontendRequest);

        $this->assertEquals($expectedRoutes['dialog'], $this->provider->getFormDialogRoute());
    }

    /**
     * @dataProvider applicationRoutesProvider
     */
    public function testGetPageRoute(bool $isFrontendRequest, array $expectedRoutes = [])
    {
        $this->frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn($isFrontendRequest);

        $this->assertEquals($expectedRoutes['page'], $this->provider->getFormPageRoute());
    }

    /**
     * @dataProvider applicationRoutesProvider
     */
    public function testGetExecutionRoute(bool $isFrontendRequest, array $expectedRoutes = [])
    {
        $this->frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn($isFrontendRequest);

        $this->assertEquals($expectedRoutes['execution'], $this->provider->getExecutionRoute());
    }

    public function applicationRoutesProvider(): array
    {
        return [
            'backend user'  => [
                'isFrontendRequest' => false,
                'expectedRoutes'    => self::DEFAULT_ROUTES
            ],
            'frontend user' => [
                'isFrontendRequest' => true,
                'expectedRoutes'    => self::FRONTEND_ROUTES
            ]
        ];
    }
}
