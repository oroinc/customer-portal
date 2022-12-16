<?php

namespace Oro\Bundle\CommerceMenuBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\CommerceMenuBundle\Model\ExtendMenuUpdate;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
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
 */
class MenuUpdate extends ExtendMenuUpdate implements
    MenuUpdateInterface
{
    use MenuUpdateTrait {
        MenuUpdateTrait::__construct as traitConstructor;
    }

    public const TARGET_URI = 'uri';
    public const TARGET_SYSTEM_PAGE = 'system_page';
    public const TARGET_CONTENT_NODE = 'content_node';
    public const TARGET_CATEGORY = 'category';
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
     * @ORM\JoinColumn(name="content_node_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
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
        parent::__construct();
        $this->traitConstructor();

        $this->menuUserAgentConditions = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getExtras()
    {
        $extras = [
            'image' => $this->getImage(),
            'screens' => $this->getScreens(),
            'condition' => $this->getCondition(),
            'divider' => $this->isDivider(),
            'userAgentConditions' => $this->getMenuUserAgentConditions(),
            'translate_disabled' => (bool) $this->getId(),
        ];

        if ($this->getTargetType() === self::TARGET_CONTENT_NODE) {
            $extras['content_node'] = $this->getContentNode();
            $extras[self::MAX_TRAVERSE_LEVEL] = $this->getMaxTraverseLevel();
        }

        if ($this->getTargetType() === self::TARGET_CATEGORY) {
            $extras['category'] = $this->getCategory();
            $extras[self::MAX_TRAVERSE_LEVEL] = $this->getMaxTraverseLevel();
        }

        if ($this->getTargetType() === self::TARGET_SYSTEM_PAGE) {
            $extras['system_page_route'] = $this->getSystemPageRoute();
        }

        if ($this->getPriority() !== null) {
            $extras['position'] = $this->getPriority();
        }

        if ($this->getIcon() !== null) {
            $extras['icon'] = $this->getIcon();
        }

        if ($this->getMenuTemplate() !== null) {
            $extras[self::MENU_TEMPLATE] = $this->getMenuTemplate();
        }

        return $extras;
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

    public function getTargetType(): ?string
    {
        if ($this->getContentNode()) {
            return self::TARGET_CONTENT_NODE;
        }

        if ($this->getCategory()) {
            return self::TARGET_CATEGORY;
        }

        if ($this->getSystemPageRoute()) {
            return self::TARGET_SYSTEM_PAGE;
        }

        if ($this->getUri()) {
            return self::TARGET_URI;
        }

        return null;
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
