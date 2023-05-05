<?php

namespace Oro\Bundle\CommerceMenuBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\NavigationBundle\Entity\MenuUpdateInterface;
use Oro\Bundle\NavigationBundle\Entity\MenuUpdateTrait;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;

/**
 * Commerce Menu Update entity
 *
 * @ORM\Entity(repositoryClass="Oro\Bundle\CommerceMenuBundle\Entity\Repository\MenuUpdateRepository")
 * @ORM\Table(
 *      name="oro_commerce_menu_upd",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="oro_commerce_menu_upd_uidx",
 *              columns={"key", "scope_id", "menu"}
 *          )
 *      }
 * )
 * @ORM\AssociationOverrides({
 *      @ORM\AssociationOverride(
 *          name="titles",
 *          joinTable=@ORM\JoinTable(
 *              name="oro_commerce_menu_upd_title",
 *              joinColumns={
 *                  @ORM\JoinColumn(
 *                      name="menu_update_id",
 *                      referencedColumnName="id",
 *                      onDelete="CASCADE"
 *                  )
 *              },
 *              inverseJoinColumns={
 *                  @ORM\JoinColumn(
 *                      name="localized_value_id",
 *                      referencedColumnName="id",
 *                      onDelete="CASCADE",
 *                      unique=true
 *                  )
 *              }
 *          )
 *      ),
 *      @ORM\AssociationOverride(
 *          name="descriptions",
 *          joinTable=@ORM\JoinTable(
 *              name="oro_commerce_menu_upd_descr",
 *              joinColumns={
 *                  @ORM\JoinColumn(
 *                      name="menu_update_id",
 *                      referencedColumnName="id",
 *                      onDelete="CASCADE"
 *                  )
 *              },
 *              inverseJoinColumns={
 *                  @ORM\JoinColumn(
 *                      name="localized_value_id",
 *                      referencedColumnName="id",
 *                      onDelete="CASCADE",
 *                      unique=true
 *                  )
 *              }
 *          )
 *      )
 * })
 * @Config(
 *      routeName="oro_commerce_menu_global_menu_index",
 *      defaultValues={
 *          "entity"={
 *              "icon"="fa-th"
 *          }
 *      }
 * )
 * @ORM\HasLifecycleCallbacks()
 *
 * @method File getImage()
 * @method MenuUpdate setImage(File $image)
 * @method MenuUpdate getTitle(Localization $localization = null)
 * @method MenuUpdate getDefaultTitle()
 * @method MenuUpdate setDefaultTitle($value)
 * @method MenuUpdate getDescription(Localization $localization = null)
 * @method MenuUpdate getDefaultDescription()
 * @method MenuUpdate setDefaultDescription($value)
 */
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

    /**
     * @var string
     *
     * @ORM\Column(name="`condition`", type="string", length=512, nullable=true)
     */
    protected $condition;

    /**
     * @var Collection|MenuUserAgentCondition[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Oro\Bundle\CommerceMenuBundle\Entity\MenuUserAgentCondition",
     *      mappedBy="menuUpdate",
     *      cascade={"ALL"},
     *      orphanRemoval=true
     * )
     */
    protected $menuUserAgentConditions;

    /**
     * @var array
     *
     * @ORM\Column(name="screens", type="array", nullable=true)
     */
    protected $screens = [];

    /**
     * @var ContentNode|null
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\WebCatalogBundle\Entity\ContentNode")
     * @ORM\JoinColumn(name="content_node_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $contentNode;

    /**
     * @var string
     *
     * @ORM\Column(name="system_page_route", type="string", length=255, nullable=true)
     */
    protected $systemPageRoute;

    /**
     * @var int
     *
     * @ORM\Column(name="link_target", type="smallint", nullable=false,
     *     options={"default"=\Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate::LINK_TARGET_SAME_WINDOW}))
     */
    protected $linkTarget = self::LINK_TARGET_SAME_WINDOW;

    /**
     * @ORM\Column(name="menu_template", type="string", length=255, nullable=true)
     */
    protected ?string $menuTemplate = null;

    /**
     * @ORM\Column(name="max_traverse_level", type="smallint", nullable=true)
     */
    protected ?int $maxTraverseLevel = null;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->traitConstructor();

        $this->menuUserAgentConditions = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * @param string|null $systemPageRoute
     *
     * @return MenuUpdate
     */
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
