<?php

namespace Oro\Bundle\FrontendBundle\Config\Validation;

use Oro\Bundle\EntityConfigBundle\Config\Validation\FieldConfigInterface;
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
            ->node('use_in_export', 'normalized_boolean')->end()
            ->node('is_displayable', 'normalized_boolean')
                ->info('`boolean` defines if the field is visible or hidden.')
            ->end()
            ->node('is_editable', 'normalized_boolean')
                ->info('`boolean` defines if the field is enabled in the storefront forms.')
            ->end()
        ;
    }
}
