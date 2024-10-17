<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Request;

use Oro\Bundle\FrontendBundle\Request\StorefrontSessionHttpKernelDecorator;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\Testing\ReflectionUtil;
use Symfony\Component\HttpFoundation\Request;

class StorefrontSessionHttpKernelDecoratorTest extends WebTestCase
{
    private StorefrontSessionHttpKernelDecorator $storefrontSessionHttpKernelDecorator;

    private string $frontendSessionName = 'TESTSFID';

    private array $originalOptions;

    protected function setUp(): void
    {
        $this->initClient();

        $this->storefrontSessionHttpKernelDecorator = self::getContainer()
            ->get('oro_frontend.security.http_kernel.session_path');

        $this->originalOptions = ReflectionUtil::getPropertyValue(
            $this->storefrontSessionHttpKernelDecorator,
            'storefrontSessionOptions'
        );
        ReflectionUtil::setPropertyValue(
            $this->storefrontSessionHttpKernelDecorator,
            'storefrontSessionOptions',
            ['name' => $this->frontendSessionName]
        );
    }

    protected function tearDown(): void
    {
        self::resetClient();

        ReflectionUtil::setPropertyValue(
            $this->storefrontSessionHttpKernelDecorator,
            'storefrontSessionOptions',
            ['name' => $this->originalOptions]
        );
    }

    public function testSessionOptionsForBackendRequest(): void
    {
        $request = Request::create($this->getUrl('oro_default'));

        $this->storefrontSessionHttpKernelDecorator->handle($request);

        $sessionOptions = self::getContainer()->getParameter('session.storage.options');
        $originalSessionOptions = self::getContainer()->getParameter('oro_security.session.storage.options');

        self::assertEquals($originalSessionOptions['name'], $sessionOptions['name']);
    }

    public function testSessionOptionsForFrontendRequest(): void
    {
        $request = Request::create($this->getUrl('oro_frontend_root'));

        $this->storefrontSessionHttpKernelDecorator->handle($request);

        $sessionOptions = self::getContainer()->getParameter('session.storage.options');
        self::assertEquals($this->frontendSessionName, $sessionOptions['name']);
    }
}
