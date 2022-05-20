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
            ->node('use_in_export', 'normalized_boolean')
                ->info('`boolean` defines if field available for export.')
                ->defaultFalse()
            ->end()
            ->node('is_displayable', 'normalized_boolean')
                ->info('`boolean` defines if the field is visible or hidden.')
                ->defaultTrue()
            ->end()
            ->node('is_editable', 'normalized_boolean')
                ->info('`boolean` defines if the field is enabled in the storefront forms.')
                ->defaultTrue()
            ->end()
            ->node('immutable', 'normalized_boolean')
                ->info('`boolean` this attribute can be used to prohibit changing the frontend state (no matter ' .
                    'whether it is enabled or not) for the entity. If TRUE than the current state cannot be changed.')
                ->defaultFalse()
            ->end()
        ;
    }
}
