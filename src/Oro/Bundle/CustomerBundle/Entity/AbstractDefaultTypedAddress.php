<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Oro\Bundle\UserBundle\Entity\User;

abstract class AbstractDefaultTypedAddress extends AbstractTypedAddress
{
    /**
     * Many-to-one relation field, relation parameters must be in specific class
     *
     * @var object
     */
    protected $frontendOwner;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $owner;

    /**
     * @var OrganizationInterface
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="system_org_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $systemOrganization;

    /**
     * {@inheritdoc}
     */
    public function getTypes()
    {
        return $this->types->map(
            function (AbstractAddressToAddressType $addressToType) {
                return $addressToType->getType();
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setTypes(Collection $types)
    {
        $this->types->clear();

        /** @var AddressType $type */
        foreach ($types as $type) {
            $this->addType($type);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeType(AddressType $type)
    {
        /** @var AbstractAddressToAddressType $addressesToType */
        foreach ($this->types as $addressesToType) {
            if ($addressesToType->getType()->getName() === $type->getName()) {
                $this->types->removeElement($addressesToType);
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addType(AddressType $type)
    {
        if (!$this->hasTypeWithName($type->getName())) {
            $addressToType = $this->createAddressToAddressTypeEntity();
            $addressToType->setType($type);
            $addressToType->setAddress($this);

            $this->types->add($addressToType);
        }

        return $this;
    }

    /**
     * Get default types
     *
     * @return Collection|AddressType[]
     */
    public function getDefaults()
    {
        $defaultTypes = new ArrayCollection();
        /** @var AbstractAddressToAddressType $addressToType */
        foreach ($this->types as $addressToType) {
            if ($addressToType->isDefault()) {
                $defaultTypes->add($addressToType->getType());
            }
        }

        return $defaultTypes;
    }

    /**
     * Checks if address has default type with specified name
     *
     * @param string $typeName
     * @return bool
     */
    public function hasDefault($typeName)
    {
        $defaultType = $this->getDefaults()->filter(
            function (AddressType $addressType) use ($typeName) {
                return $addressType->getName() === $typeName;
            }
        );
        return false === $defaultType->isEmpty();
    }

    /**
     * Set default types
     *
     * @param Collection|AddressType[] $defaults
     * @return AbstractDefaultTypedAddress
     */
    public function setDefaults($defaults)
    {
        $defaultTypes = [];
        foreach ($defaults as $default) {
            $defaultTypes[$default->getName()] = true;
        }

        /** @var AbstractAddressToAddressType $addressToType */
        foreach ($this->types as $addressToType) {
            $addressToType->setDefault(!empty($defaultTypes[$addressToType->getType()->getName()]));
        }

        return $this;
    }

    /**
     * Set frontend owner.
     *
     * @param object $frontendOwner
     * @return $this
     */
    public function setFrontendOwner($frontendOwner = null)
    {
        $this->frontendOwner = $frontendOwner;

        return $this;
    }

    /**
     * Get frontend owner.
     *
     * @return object
     */
    public function getFrontendOwner()
    {
        return $this->frontendOwner;
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param User $owner
     *
     * @return $this
     */
    public function setOwner(User $owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Return entity for many-to-many relationship.
     * Should be compatible with AbstractAddressToAddressType
     *
     * @return AbstractAddressToAddressType
     */
    abstract protected function createAddressToAddressTypeEntity();

    /**
     * @return OrganizationInterface
     */
    public function getSystemOrganization()
    {
        return $this->systemOrganization;
    }

    /**
     * @param OrganizationInterface $systemOrganization
     * @return AbstractDefaultTypedAddress
     */
    public function setSystemOrganization(OrganizationInterface $systemOrganization = null)
    {
        $this->systemOrganization = $systemOrganization;

        return $this;
    }
}
