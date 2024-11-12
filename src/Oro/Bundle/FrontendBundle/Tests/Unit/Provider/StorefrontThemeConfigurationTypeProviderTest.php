<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Provider;

use Oro\Bundle\FrontendBundle\Provider\StorefrontThemeConfigurationTypeProvider;
use PHPUnit\Framework\TestCase;

final class StorefrontThemeConfigurationTypeProviderTest extends TestCase
{
    private StorefrontThemeConfigurationTypeProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->provider = new StorefrontThemeConfigurationTypeProvider();
    }

    public function testGetType(): void
    {
        self::assertEquals(StorefrontThemeConfigurationTypeProvider::STOREFRONT, $this->provider->getType());
    }

    public function testGetLabel(): void
    {
        self::assertEquals('oro_frontend.theme.themeconfiguration.types.storefront.label', $this->provider->getLabel());
    }
}
