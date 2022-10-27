<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Placeholder;

use Oro\Bundle\DistributionBundle\Handler\ApplicationState;
use Oro\Bundle\FrontendBundle\Placeholder\FrontendFilter;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class FrontendFilterTest extends \PHPUnit\Framework\TestCase
{
    private const BACKEND_PREFIX = '/admin';

    private function getFilter(Request $currentRequest = null): FrontendFilter
    {
        $requestStack = new RequestStack();
        if (null !== $currentRequest) {
            $requestStack->push($currentRequest);
        }
        $applicationState = $this->createMock(ApplicationState::class);
        $applicationState->expects(self::any())
            ->method('isInstalled')
            ->willReturn(true);

        return new FrontendFilter(new FrontendHelper(self::BACKEND_PREFIX, $requestStack, $applicationState));
    }

    public function testNoRequestBehaviour()
    {
        $filter = $this->getFilter();
        $this->assertTrue($filter->isBackendRoute());
        $this->assertFalse($filter->isFrontendRoute());
    }

    /**
     * @dataProvider isBackendIsFrontendDataProvider
     */
    public function testIsBackendIsFrontend(string $path, bool $isFrontend)
    {
        $request = Request::create($path);

        $filter = $this->getFilter($request);
        $this->assertSame(!$isFrontend, $filter->isBackendRoute());
        $this->assertSame($isFrontend, $filter->isFrontendRoute());
    }

    public function isBackendIsFrontendDataProvider(): array
    {
        return [
            'backend request' => [
                'path' => self::BACKEND_PREFIX . '/backend',
                'isFrontend' => false,
            ],
            'frontend request' => [
                'path' => '/frontend',
                'isFrontend' => true,
            ],
        ];
    }
}
