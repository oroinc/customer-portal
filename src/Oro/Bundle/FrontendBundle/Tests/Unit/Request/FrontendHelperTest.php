<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Request;

use Oro\Bundle\DistributionBundle\Handler\ApplicationState;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class FrontendHelperTest extends TestCase
{
    private const BACKEND_PREFIX = '/admin';

    private ApplicationState&MockObject $applicationState;

    #[\Override]
    protected function setUp(): void
    {
        $this->applicationState = $this->createApplicationState(true);
    }

    private function getRequestStack(?Request $currentRequest = null): RequestStack
    {
        $requestStack = new RequestStack();
        if (null !== $currentRequest) {
            $requestStack->push($currentRequest);
        }

        return $requestStack;
    }

    /**
     * @dataProvider isFrontendRequestDataProvider
     */
    public function testIsFrontendRequest(string $path, bool $isFrontend): void
    {
        $request = Request::create($path);

        $helper = new FrontendHelper(self::BACKEND_PREFIX, $this->getRequestStack($request), $this->applicationState);
        $this->assertSame($isFrontend, $helper->isFrontendRequest());
    }

    public function isFrontendRequestDataProvider(): array
    {
        return [
            'backend'                             => [
                'path'       => self::BACKEND_PREFIX . '/backend',
                'isFrontend' => false
            ],
            'frontend'                            => [
                'path'       => '/frontend',
                'isFrontend' => true
            ],
            'frontend with backend part'          => [
                'path'       => '/frontend' . self::BACKEND_PREFIX,
                'isFrontend' => true
            ],
            'frontend with backend part and slug' => [
                'path'       => '/frontend' . self::BACKEND_PREFIX . '/slug',
                'isFrontend' => true
            ],
            'frontend that starts with backend part' => [
                'path'       => self::BACKEND_PREFIX . 'instration',
                'isFrontend' => true
            ],
            'backend that is the same as backend part' => [
                'path'       => self::BACKEND_PREFIX,
                'isFrontend' => false
            ]
        ];
    }

    public function testIsFrontendRequestWithoutPath(): void
    {
        $helper = new FrontendHelper(self::BACKEND_PREFIX, $this->getRequestStack(), $this->applicationState);
        $this->assertFalse($helper->isFrontendRequest());
    }

    public function testIsFrontendUrlForBackendUrl(): void
    {
        $helper = new FrontendHelper(self::BACKEND_PREFIX, $this->getRequestStack(), $this->applicationState);
        $this->assertFalse($helper->isFrontendUrl(self::BACKEND_PREFIX . '/test'));
    }

    public function testIsFrontendUrl(): void
    {
        $helper = new FrontendHelper(self::BACKEND_PREFIX, $this->getRequestStack(), $this->applicationState);
        $this->assertTrue($helper->isFrontendUrl('/test'));
    }

    public function testEmulateFrontendRequest(): void
    {
        $request = Request::create(self::BACKEND_PREFIX . '/backend');

        $helper = new FrontendHelper(self::BACKEND_PREFIX, $this->getRequestStack($request), $this->applicationState);
        $helper->emulateFrontendRequest();
        $this->assertTrue($helper->isFrontendRequest());

        $helper->resetRequestEmulation();
        $this->assertFalse($helper->isFrontendRequest());
    }

    public function testEmulateBackendRequest(): void
    {
        $request = Request::create('/frontend');

        $helper = new FrontendHelper(self::BACKEND_PREFIX, $this->getRequestStack($request), $this->applicationState);
        $helper->emulateBackendRequest();
        $this->assertFalse($helper->isFrontendRequest());

        $helper->resetRequestEmulation();
        $this->assertTrue($helper->isFrontendRequest());
    }

    public function testEmulationHasPriorityOverInstallCheck(): void
    {
        $request = Request::create('/frontend');
        $appState = $this->createApplicationState(false);

        $helper = new FrontendHelper(self::BACKEND_PREFIX, $this->getRequestStack($request), $appState);
        $helper->emulateFrontendRequest();
        $this->assertTrue($helper->isFrontendRequest());
        $helper->emulateBackendRequest();
        $this->assertFalse($helper->isFrontendRequest());
    }

    private function createApplicationState(bool $isInstalled): ApplicationState|MockObject
    {
        $applicationState = $this->createMock(ApplicationState::class);
        $applicationState->expects(self::any())
            ->method('isInstalled')
            ->willReturn($isInstalled);

        return $applicationState;
    }
}
