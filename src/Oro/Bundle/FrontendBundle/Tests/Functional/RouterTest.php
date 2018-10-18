<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class RouterTest extends WebTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->initClient();
    }

    public function testRouteNames()
    {
        $router = $this->getContainer()->get('oro_test.router.default.alias');
        $generator = $router->getGenerator();
        $this->assertInstanceOf('\srcTestProjectContainerUrlGenerator', $generator);

        $declaredRoutesProperty = new \ReflectionProperty(get_class($generator), 'declaredRoutes');
        $declaredRoutesProperty->setAccessible(true);
        $declaredRoutes = $declaredRoutesProperty->getValue();

        $invalidRoutes = array_filter(
            array_keys($declaredRoutes),
            function ($name) {
                return strpos($name, 'orob2b') === 0;
            }
        );
        $this->assertEmpty(
            $invalidRoutes,
            "Invalid route names:\n" . implode("\n", $invalidRoutes)
        );
    }
}
