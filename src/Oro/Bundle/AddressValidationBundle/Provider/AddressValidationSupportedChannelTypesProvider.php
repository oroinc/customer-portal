<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\Provider;

/**
 * Provides the integration channel types supporting the address validation feature.
 */
class AddressValidationSupportedChannelTypesProvider
{
    /**
     * @param array<string> $channelTypes
     */
    public function __construct(private array $channelTypes = [])
    {
    }

    /**
     * @return array<string>
     */
    public function getChannelTypes(): array
    {
        return $this->channelTypes;
    }
}
