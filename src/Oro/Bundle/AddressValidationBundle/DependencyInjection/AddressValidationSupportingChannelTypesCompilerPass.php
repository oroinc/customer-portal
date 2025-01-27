<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\DependencyInjection;

use Oro\Bundle\IntegrationBundle\Exception\LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Compiler pass adding the integration channel types supporting the address validation feature.
 */
class AddressValidationSupportingChannelTypesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $serviceName = 'oro_address_validation.provider.supported_channel_types';
        if (!$container->hasDefinition($serviceName)) {
            return;
        }

        $tagName = 'oro_address_validation.channel';
        $channels = $container->findTaggedServiceIds($tagName);
        $channelTypes = [];

        foreach ($channels as $serviceId => $tags) {
            foreach ($tags as $tagAttrs) {
                if (!isset($tagAttrs['type'])) {
                    throw new LogicException(
                        sprintf(
                            '"type" attribute is expected to be set for tag "%s" in service "%s"',
                            $tagName,
                            $serviceId
                        )
                    );
                }

                $channelTypes[] = $tagAttrs['type'];
            }
        }

        if ($channelTypes) {
            $container
                ->getDefinition($serviceName)
                ->setArgument('$channelTypes', $channelTypes);
        }
    }
}
