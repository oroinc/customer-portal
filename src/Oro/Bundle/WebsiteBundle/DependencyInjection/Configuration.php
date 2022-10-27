<?php

namespace Oro\Bundle\WebsiteBundle\DependencyInjection;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    const URL = 'url';
    const SECURE_URL = 'secure_url';

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('oro_website');
        $rootNode = $treeBuilder->getRootNode();

        SettingsBuilder::append(
            $rootNode,
            [
                self::URL => ['type' => 'text', 'value' => ''],
                self::SECURE_URL => ['type' => 'text', 'value' => ''],
            ]
        );

        return $treeBuilder;
    }
}
