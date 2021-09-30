<?php

namespace Oro\Bundle\FrontendAttachmentBundle\Config\Validation;

use Oro\Bundle\EntityConfigBundle\Config\Validation\FieldConfigInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * Provides validations field config for attachment scope.
 */
class AttachmentFieldConfiguration implements FieldConfigInterface
{
    public function getSectionName(): string
    {
        return 'attachment';
    }

    public function configure(NodeBuilder $nodeBuilder): void
    {
        $nodeBuilder
            ->node('acl_protected', 'normalized_boolean')->end()
            ->arrayNode('file_applications')
                ->ignoreExtraKeys()
            ->end()
        ;
    }
}
