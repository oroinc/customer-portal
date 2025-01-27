<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\DependencyInjection;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
use Oro\Bundle\ConfigBundle\Utils\TreeUtils;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const string ROOT_NODE = 'oro_address_validation';
    public const string ADDRESS_VALIDATION_SERVICE = 'address_validation_service';

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(static::ROOT_NODE);

        $rootNode = $treeBuilder->getRootNode();

        SettingsBuilder::append(
            $rootNode,
            [
                static::ADDRESS_VALIDATION_SERVICE => [
                    'value' => null,
                    'type' => 'integer',
                ],
            ]
        );

        return $treeBuilder;
    }

    public static function getConfigKeyByName(string $name): string
    {
        return TreeUtils::getConfigKey(static::ROOT_NODE, $name);
    }
}
