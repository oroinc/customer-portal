<?php

namespace Oro\Bundle\FrontendBundle\EntityConfig;

use Oro\Bundle\EntityConfigBundle\EntityConfig\FieldConfigInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * Provides validations field config for frontend scope.
 */
class FrontendFieldConfiguration implements FieldConfigInterface
{
    public function getSectionName(): string
    {
        return 'frontend';
    }

    public function configure(NodeBuilder $nodeBuilder): void
    {
        $nodeBuilder
            ->booleanNode('use_in_export')
                ->info('`boolean` defines if field available for export.')
                ->defaultFalse()
            ->end()
            ->booleanNode('is_displayable')
                ->info('`boolean` defines if the field is visible or hidden.')
                ->defaultTrue()
            ->end()
            ->booleanNode('is_editable')
                ->info('`boolean` defines if the field is enabled in the storefront forms.')
                ->defaultTrue()
            ->end()
        ;
    }
}
