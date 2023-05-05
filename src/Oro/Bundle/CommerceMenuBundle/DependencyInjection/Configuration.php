<?php

namespace Oro\Bundle\CommerceMenuBundle\DependencyInjection;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const ROOT_NODE = 'oro_commerce_menu';
    public const MAIN_NAVIGATION_MENU = 'main_navigation_menu';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(self::ROOT_NODE);
        $rootNode = $treeBuilder->getRootNode();

        SettingsBuilder::append(
            $rootNode,
            [
                self::MAIN_NAVIGATION_MENU => ['type' => 'string', 'value' => 'commerce_main_menu'],
            ]
        );

        return $treeBuilder;
    }

    public static function getConfigKeyByName(string $key): string
    {
        return implode(ConfigManager::SECTION_MODEL_SEPARATOR, [self::ROOT_NODE, $key]);
    }
}
