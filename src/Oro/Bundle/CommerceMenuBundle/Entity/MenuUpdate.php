<?php

namespace Oro\Bundle\CommerceMenuBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroCommerceMenuBundle_Entity_MenuUpdate;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\CommerceMenuBundle\Entity\Repository\MenuUpdateRepository;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\NavigationBundle\Entity\MenuUpdateInterface;
use Oro\Bundle\NavigationBundle\Entity\MenuUpdateTrait;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;

/**
 * Commerce Menu Update entity
 *
 *
 * @method File getImage()
 * @method MenuUpdate setImage(File $image)
 * @method MenuUpdate getTitle(Localization $localization = null)
 * @method MenuUpdate getDefaultTitle()
 * @method MenuUpdate setDefaultTitle($value)
 * @method MenuUpdate getDescription(Localization $localization = null)
 * @method MenuUpdate getDefaultDescription()
 * @method MenuUpdate setDefaultDescription($value)
 * @mixin OroCommerceMenuBundle_Entity_MenuUpdate
 */
#[ORM\Entity(repositoryClass: MenuUpdateRepository::class)]
#[ORM\Table(name: 'oro_commerce_menu_upd')]
#[ORM\UniqueConstraint(name: 'oro_commerce_menu_upd_uidx', columns: ['key', 'scope_id', 'menu'])]
#[ORM\AssociationOverrides([
    new ORM\AssociationOverride(
        name: 'titles',
        joinColumns: [
        new ORM\JoinColumn(
            name: 'menu_update_id',
            referencedColumnName: 'id',
            onDelete: 'CASCADE'
        )
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(
                name: 'localized_value_id',
                referencedColumnName: 'id',
                unique: true,
                onDelete: 'CASCADE'
            )
        ],
        joinTable: new ORM\JoinTable(name: 'oro_commerce_menu_upd_title')
    ),
    new ORM\AssociationOverride(
        name: 'descriptions',
        joinColumns: [
        new ORM\JoinColumn(
            name: 'menu_update_id',
            referencedColumnName: 'id',
            onDelete: 'CASCADE'
        )
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(
                name: 'localized_value_id',
                referencedColumnName: 'id',
                unique: true,
                onDelete: 'CASCADE'
            )
        ],
        joinTable: new ORM\JoinTable(name: 'oro_commerce_menu_upd_descr')
    )
])]
#[ORM\HasLifecycleCallbacks]
#[Config(
    routeName: 'oro_commerce_menu_global_menu_index',
    defaultValues: ['entity' => ['icon' => 'fa-th']]
)]
class MenuUpdate implements
    MenuUpdateInterface,
    ExtendEntityInterface
{
    use MenuUpdateTrait {
        MenuUpdateTrait::__construct as traitConstructor;
    }
    use ExtendEntityTrait;

    public const TARGET_NONE = 'none';
    public const TARGET_URI = 'uri';
    public const TARGET_SYSTEM_PAGE = 'system_page';
    public const TARGET_CONTENT_NODE = 'content_node';
    public const TARGET_CATEGORY = 'category';

    public const SYSTEM_PAGE_ROUTE = 'system_page_route';

    public const IMAGE = 'image';
    public const SCREENS = 'screens';
    public const CONDITION = 'condition';
    public const USER_AGENT_CONDITIONS = 'userAgentConditions';

    public const LINK_TARGET_NEW_WINDOW = 0;
    public const LINK_TARGET_SAME_WINDOW = 1;

    public const MENU_TEMPLATE = 'menu_template';
    public const MAX_TRAVERSE_LEVEL = 'max_traverse_level';

    #[ORM\Column(name: '`condition`', type: Types::STRING, length: 512, nullable: true)]
    protected ?string $condition = null;

    /**
     * @var Collection<int, MenuUserAgentCondition>
     */
    #[ORM\OneToMany(
        mappedBy: 'menuUpdate',
        targetEntity: MenuUserAgentCondition::class,
        cascade: ['ALL'],
        orphanRemoval: true
    )]
    protected ?Collection $menuUserAgentConditions = null;

    /**
     * @var array
     */
    #[ORM\Column(name: 'screens', type: Types::ARRAY, nullable: true)]
    protected $screens = [];

    #[ORM\ManyToOne(targetEntity: ContentNode::class, inversedBy: 'referencedMenuItems')]
    #[ORM\JoinColumn(name: 'content_node_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    protected ?ContentNode $contentNode = null;

    #[ORM\Column(name: 'system_page_route', type: Types::STRING, length: 255, nullable: true)]
    protected ?string $systemPageRoute = null;

    #[ORM\Column(
        name: 'link_target',
        type: Types::SMALLINT,
        nullable: false,
        options: ['default' => MenuUpdate::LINK_TARGET_SAME_WINDOW]
    )]
    protected int $linkTarget = self::LINK_TARGET_SAME_WINDOW;

    #[ORM\Column(name: 'menu_template', type: Types::STRING, length: 255, nullable: true)]
    protected ?string $menuTemplate = null;

    #[ORM\Column(name: 'max_traverse_level', type: Types::SMALLINT, nullable: true)]
    protected ?int $maxTraverseLevel = null;

    public function __construct()
    {
        $this->traitConstructor();

        $this->menuUserAgentConditions = new ArrayCollection();
    }

    #[\Override]
    public function getLinkAttributes(): array
    {
        $linkAttributes = [];

        if ($this->getLinkTarget() === self::LINK_TARGET_NEW_WINDOW) {
            $linkAttributes['target'] = '_blank';
        }

        return $linkAttributes;
    }

    /**
     * @return string
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * @param string $condition
     * @return MenuUpdate
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;

        return $this;
    }

    /**
     * @return MenuUserAgentCondition[]|Collection
     */
    public function getMenuUserAgentConditions()
    {
        return $this->menuUserAgentConditions;
    }

    /**
     * @param MenuUserAgentCondition $menuUserAgentCondition
     * @return MenuUpdate
     */
    public function addMenuUserAgentCondition(MenuUserAgentCondition $menuUserAgentCondition)
    {
        if (!$this->menuUserAgentConditions->contains($menuUserAgentCondition)) {
            $menuUserAgentCondition->setMenuUpdate($this);
            $this->menuUserAgentConditions->add($menuUserAgentCondition);
        }

        return $this;
    }

    /**
     * @param MenuUserAgentCondition $menuUserAgentCondition
     * @return MenuUpdate
     */
    public function removeMenuUserAgentCondition(MenuUserAgentCondition $menuUserAgentCondition)
    {
        if ($this->menuUserAgentConditions->contains($menuUserAgentCondition)) {
            $this->menuUserAgentConditions->removeElement($menuUserAgentCondition);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getScreens()
    {
        return (array) $this->screens;
    }

    /**
     * @param array $screens
     * @return MenuUpdate
     */
    public function setScreens(array $screens)
    {
        $this->screens = $screens;

        return $this;
    }

    public function setContentNode(?ContentNode $contentNode): self
    {
        $this->contentNode = $contentNode;

        return $this;
    }

    public function getContentNode(): ?ContentNode
    {
        return $this->contentNode;
    }

    public function setSystemPageRoute(?string $systemPageRoute): self
    {
        $this->systemPageRoute = $systemPageRoute;

        return $this;
    }

    public function getSystemPageRoute(): ?string
    {
        return $this->systemPageRoute;
    }

    public function getLinkTarget(): int
    {
        return $this->linkTarget;
    }

    public function setLinkTarget(int $linkTarget): self
    {
        $this->linkTarget = $linkTarget;

        return $this;
    }

    public function getMenuTemplate(): ?string
    {
        return $this->menuTemplate;
    }

    public function setMenuTemplate(?string $menuTemplate): self
    {
        $this->menuTemplate = $menuTemplate;

        return $this;
    }

    public function setMaxTraverseLevel(?int $maxTraverseLevel): self
    {
        $this->maxTraverseLevel = $maxTraverseLevel;

        return $this;
    }

    public function getMaxTraverseLevel(): ?int
    {
        return $this->maxTraverseLevel;
    }
}
