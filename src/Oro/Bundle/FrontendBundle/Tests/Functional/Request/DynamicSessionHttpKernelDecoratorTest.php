<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Request;

use Oro\Bundle\FrontendBundle\Request\DynamicSessionHttpKernelDecorator;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class DynamicSessionHttpKernelDecoratorTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
    }

    public function testContainerShouldBePossibleToChangeSessionOptionsForFrontendRequest()
    {
        $request = Request::create($this->getUrl('oro_frontend_root'));

        $frontendHelper = $this->createMock(FrontendHelper::class);
        $frontendHelper->expects(self::once())
            ->method('isFrontendUrl')
            ->with($request->getPathInfo())
            ->willReturn(true);

        $frontendSessionName = 'TESTSFID';
        $container = (self::$kernel ?? self::bootKernel())->getContainer();
        $kernelDecorator = new DynamicSessionHttpKernelDecorator(
            $container->get('kernel'),
            $container,
            $frontendHelper,
            ['name' => $frontendSessionName]
        );

        $kernelDecorator->handle($request);

        $sessionOptions = $container->getParameter('session.storage.options');
        self::assertEquals($frontendSessionName, $sessionOptions['name']);
    }
}
