<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Provider;

use Oro\Bundle\FrontendBundle\Provider\HomePageProvider;
use PHPUnit\Framework\TestCase;

class HomePageProviderTest extends TestCase
{
    private HomePageProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->provider = new HomePageProvider();
    }

    public function testGetHomePageNotFoundInSystemConfiguration(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('An appropriate home page provider should be implemented.');

        $this->provider->getHomePage();
    }
}
