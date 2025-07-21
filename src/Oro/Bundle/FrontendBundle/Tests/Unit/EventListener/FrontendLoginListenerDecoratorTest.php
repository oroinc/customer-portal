<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\EventListener;

use Oro\Bundle\FrontendBundle\EventListener\FrontendLoginListenerDecorator;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\MessageQueueBundle\EventListener\LoginListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class FrontendLoginListenerDecoratorTest extends TestCase
{
    private KernelInterface&MockObject $kernel;
    private LoginListener&MockObject $baseLoginListener;
    private FrontendHelper&MockObject $frontendHelper;
    private Request $request;
    private InteractiveLoginEvent $event;
    private FrontendLoginListenerDecorator $loginListener;

    #[\Override]
    protected function setUp(): void
    {
        $this->kernel = $this->createMock(KernelInterface::class);
        $this->baseLoginListener = $this->createMock(LoginListener::class);
        $this->frontendHelper = $this->createMock(FrontendHelper::class);
        $this->request = new Request();
        $this->event = new InteractiveLoginEvent($this->request, $this->createMock(TokenInterface::class));

        $this->loginListener = new FrontendLoginListenerDecorator(
            $this->kernel,
            $this->baseLoginListener,
            $this->frontendHelper
        );
    }

    /**
     * @dataProvider onLoginProvider
     */
    public function testOnLogin(bool $frontend, string $env, bool $expected): void
    {
        $this->kernel->expects(self::any())
            ->method('getEnvironment')
            ->willReturn($env);

        $this->frontendHelper->expects(self::any())
            ->method('isFrontendUrl')
            ->with($this->request->getPathInfo())
            ->willReturn($frontend);

        if ($expected) {
            $this->baseLoginListener->expects(self::once())
                ->method('onLogin')
                ->with($this->event);
        } else {
            $this->baseLoginListener->expects(self::never())
                ->method('onLogin');
        }

        $this->loginListener->onLogin($this->event);
    }

    public function onLoginProvider(): array
    {
        return [
            'frontend request, prod env' => [
                'frontend' => true,
                'env'      => 'prod',
                'expected' => false
            ],
            'frontend request, dev env'  => [
                'frontend' => true,
                'env'      => 'dev',
                'expected' => true
            ],
            'backend request, prod env'  => [
                'frontend' => false,
                'env'      => 'prod',
                'expected' => true
            ],
            'backend request, dev env'   => [
                'frontend' => false,
                'env'      => 'dev',
                'expected' => true
            ]
        ];
    }
}
