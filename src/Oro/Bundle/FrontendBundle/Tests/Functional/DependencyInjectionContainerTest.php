<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;

class DependencyInjectionContainerTest extends WebTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->initClient();
        $this->client->useHashNavigation(true);
    }

    public function testParameterAndServiceNames()
    {
        /** @var Container $container */
        $container = $this->getContainer();

        $invalidParameters = array_filter(
            array_keys($container->getParameterBag()->all()),
            function ($name) {
                return str_starts_with($name, 'orob2b');
            }
        );
        $this->assertEmpty(
            $invalidParameters,
            "Invalid parameter names:\n" . implode("\n", $invalidParameters)
        );

        $invalidServices = array_filter(
            $container->getServiceIds(),
            function ($name) {
                return str_starts_with($name, 'orob2b');
            }
        );
        $this->assertEmpty(
            $invalidServices,
            "Invalid service names:\n" . implode("\n", $invalidParameters)
        );
    }
}
