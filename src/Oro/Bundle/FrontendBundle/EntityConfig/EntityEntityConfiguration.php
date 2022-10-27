<?php

namespace Oro\Bundle\FrontendBundle\EntityConfig;

use Oro\Bundle\EntityConfigBundle\EntityConfig\EntityConfigInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * Provides validations entity config for entity scope.
 */
class EntityEntityConfiguration implements EntityConfigInterface
{
    public function getSectionName(): string
    {
        return 'entity';
    }

    public function configure(NodeBuilder $nodeBuilder): void
    {
        $nodeBuilder
            ->scalarNode('frontend_grid_all_view_label')
                ->info('`string` changes all view label of the entity for frontend.')
            ->end()
        ;
    }
}
