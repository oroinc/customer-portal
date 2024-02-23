<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Model;

/**
 * DTO that represents a quick access button config in system configuration.
 */
class QuickAccessButtonConfig
{
    public const TYPE_MENU = 'menu';
    public const TYPE_WEB_CATALOG_NODE = 'web_catalog_node';
    /** MENU_NOT_RESOLVED constant is used to indicate if menu based on configuration can not be build */
    public const MENU_NOT_RESOLVED = 'menu_not_resolved';

    private array $label = [];
    private ?string $type = null;
    private ?string $menu = null;
    private ?int $webCatalogNode = null;

    public function getLabel(): array
    {
        return $this->label;
    }

    public function setLabel(array $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getMenu(): ?string
    {
        return $this->menu;
    }

    public function setMenu(?string $menu): self
    {
        $this->menu = $menu;

        return $this;
    }

    public function getWebCatalogNode(): ?int
    {
        return $this->webCatalogNode;
    }

    public function setWebCatalogNode(?int $webCatalogNode): self
    {
        $this->webCatalogNode = $webCatalogNode;

        return $this;
    }
}
