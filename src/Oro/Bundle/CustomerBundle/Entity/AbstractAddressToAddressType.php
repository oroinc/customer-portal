<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\ConfigField;

/**
 * Abstract Typed address.
 */
#[ORM\MappedSuperclass]
abstract class AbstractAddressToAddressType
{
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    /**
     * Many-to-one relation field, relation parameters must be in specific class
     *
     * @var AbstractDefaultTypedAddress
     */
    protected $address;

    #[ORM\ManyToOne(targetEntity: AddressType::class)]
    #[ORM\JoinColumn(name: 'type_name', referencedColumnName: 'name', onDelete: 'CASCADE')]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true]])]
    protected ?AddressType $type = null;

    #[ORM\Column(name: 'is_default', type: Types::BOOLEAN, nullable: true)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true]])]
    protected ?bool $default = null;

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
