<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class RouterTest extends WebTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->initClient();
    }

    public function testRouteNames(): void
    {
        $routeCollection = $this->getContainer()->get('router')->getRouteCollection();
        $this->assertNotNull($routeCollection);

        $invalidRoutes = array_filter(
            array_keys(iterator_to_array($routeCollection)),
            function ($name) {
                return str_starts_with($name, 'orob2b');
            }
        );

        $this->assertEmpty(
            $invalidRoutes,
            "Invalid route names:\n" . implode("\n", $invalidRoutes)
        );
    }
}
