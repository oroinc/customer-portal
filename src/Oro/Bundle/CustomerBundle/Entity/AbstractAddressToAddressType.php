<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

/**
 * Abstract Typed address.
 *
 * @ORM\MappedSuperclass
 */
abstract class AbstractAddressToAddressType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Many-to-one relation field, relation parameters must be in specific class
     *
     * @var AbstractDefaultTypedAddress
     */
    protected $address;

    /**
     * @var AddressType
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\AddressBundle\Entity\AddressType")
     * @ORM\JoinColumn(name="type_name", referencedColumnName="name", onDelete="CASCADE")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $type;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_default", type="boolean", nullable=true)
     *
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $default;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set address
     *
     * @param AbstractDefaultTypedAddress $address
     * @return AbstractAddressToAddressType
     */
    public function setAddress(AbstractDefaultTypedAddress $address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return AbstractDefaultTypedAddress
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set type
     *
     * @param AddressType $type
     * @return AbstractAddressToAddressType
     */
    public function setType(AddressType $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return AddressType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set default
     *
     * @param boolean $default
     * @return AbstractAddressToAddressType
     */
    public function setDefault($default)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * Get default
     *
     * @return boolean
     */
    public function isDefault()
    {
        return $this->default;
    }
}
