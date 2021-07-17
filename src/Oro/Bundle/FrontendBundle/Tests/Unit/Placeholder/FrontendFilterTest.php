<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Placeholder;

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

        return new FrontendFilter(new FrontendHelper(self::BACKEND_PREFIX, $requestStack));
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

    /**
     * @return array
     */
    public function isBackendIsFrontendDataProvider()
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
