<?php

namespace Oro\Bundle\CommerceMenuBundle\Api\Model;

use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;

/**
 * Represents a storefront menu item.
 */
final class CommerceMenuItem
{
    public function __construct(
        private readonly string $name,
        private readonly string $label,
        private readonly ?string $uri = null,
        private readonly ?string $description = null,
        private readonly array $extras = [],
        private readonly array $linkAttributes = [],
        private readonly ?string $parentName = null,
        private readonly ?ContentNode $contentNode = null
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getExtras(): array
    {
        return $this->extras;
    }

    public function getLinkAttributes(): array
    {
        return $this->linkAttributes;
    }

    public function getParentName(): ?string
    {
        return $this->parentName;
    }

    public function getContentNode(): ?ContentNode
    {
        return $this->contentNode;
    }
}
