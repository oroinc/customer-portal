<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\Tests\Unit\Provider;

use Oro\Bundle\AddressValidationBundle\Provider\AddressValidationSupportedChannelTypesProvider;
use PHPUnit\Framework\TestCase;

final class AddressValidationSupportedChannelTypesProviderTest extends TestCase
{
    public function testGetChannelTypesReturnsProvidedChannelTypes(): void
    {
        $channelTypes = ['type1', 'type2', 'type3'];

        $provider = new AddressValidationSupportedChannelTypesProvider($channelTypes);

        self::assertSame($channelTypes, $provider->getChannelTypes());
    }

    public function testGetChannelTypesWithNoChannelTypesReturnsEmptyArray(): void
    {
        $provider = new AddressValidationSupportedChannelTypesProvider();

        self::assertSame([], $provider->getChannelTypes());
    }
}
