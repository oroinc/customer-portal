<?php

namespace Oro\Bundle\CommerceMenuBundle\Api\Model;

use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;

/**
 * Represents a menu item data structure.
 * Contains properties from ItemInterface.
 */
final readonly class CommerceMenuItem
{
    public function __construct(
        private string $name,
        private string $label,
        private ?string $uri = null,
        private ?string $description = null,
        private array $extras = [],
        private array $link_attributes = [],
        private ?string $parentName = null,
        private ?ContentNode $contentNode = null,
        private ?array $resource = null,
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

    public function getParentName(): ?string
    {
        return $this->parentName;
    }

    public function getContentNode(): ?ContentNode
    {
        return $this->contentNode;
    }

    public function getResource(): ?array
    {
        return $this->resource;
    }

    public function getLinkAttributes(): array
    {
        return $this->link_attributes;
    }
}
