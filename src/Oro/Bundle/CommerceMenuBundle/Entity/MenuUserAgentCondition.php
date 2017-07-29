<?php

namespace Oro\Bundle\CommerceMenuBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="oro_menu_user_agent_condition")
 */
class MenuUserAgentCondition
{
    const OPERATION_CONTAINS = 'contains';
    const OPERATION_DOES_NOT_CONTAIN = 'does_not_contain';
    const OPERATION_MATCHES = 'matches';
    const OPERATION_DOES_NOT_MATCHES = 'does_not_match';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var MenuUpdate
     *
     * @ORM\ManyToOne(targetEntity="MenuUpdate", inversedBy="menuUserAgentConditions")
     * @ORM\JoinColumn(name="menu_update_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $menuUpdate;

    /**
     * @var integer $conditionGroupIdentifier
     *
     * @ORM\Column(name="condition_group_identifier", type="integer")
     */
    protected $conditionGroupIdentifier;

    /**
     * @var string $operation
     *
     * @ORM\Column(name="operation", type="string", length=32)
     */
    protected $operation;

    /**
     * @var string $value
     *
     * @ORM\Column(name="value", type="string", length=255)
     */
    protected $value;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return MenuUpdate
     */
    public function getMenuUpdate()
    {
        return $this->menuUpdate;
    }

    /**
     * @param MenuUpdate $menuUpdate
     *
     * @return $this
     */
    public function setMenuUpdate(MenuUpdate $menuUpdate)
    {
        $this->menuUpdate = $menuUpdate;

        return $this;
    }

    /**
     * @return integer
     */
    public function getConditionGroupIdentifier()
    {
        return $this->conditionGroupIdentifier;
    }

    /**
     * @param integer $conditionGroupIdentifier
     *
     * @return self
     */
    public function setConditionGroupIdentifier($conditionGroupIdentifier)
    {
        $this->conditionGroupIdentifier = $conditionGroupIdentifier;

        return $this;
    }

    /**
     * @return string
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * @param string $operation
     *
     * @return self
     */
    public function setOperation($operation)
    {
        $this->operation = $operation;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return self
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }
}
