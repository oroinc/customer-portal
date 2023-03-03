<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Entity represents Customer and handles all related mappings
 *
 * @ORM\Entity(repositoryClass="Oro\Bundle\CustomerBundle\Entity\Repository\CustomerRepository")
 * @ORM\Table(
 *      name="oro_customer",
 *      indexes={
 *          @ORM\Index(name="oro_customer_name_idx", columns={"name"}),
 *          @ORM\Index(name="idx_oro_customer_created_at", columns={"created_at"}),
 *          @ORM\Index(name="idx_oro_customer_updated_at", columns={"updated_at"}),
 *      }
 * )
 *
 * @Config(
 *      routeName="oro_customer_customer_index",
 *      routeView="oro_customer_customer_view",
 *      routeCreate="oro_customer_customer_create",
 *      routeUpdate="oro_customer_customer_update",
 *      defaultValues={
 *          "entity"={
 *              "icon"="fa-building"
 *          },
 *          "ownership"={
 *              "owner_type"="USER",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="owner_id",
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id",
 *              "frontend_owner_type"="FRONTEND_CUSTOMER",
 *              "frontend_owner_field_name"="parent",
 *              "frontend_owner_column_name"="parent_id",
 *          },
 *          "form"={
 *              "form_type"="Oro\Bundle\CustomerBundle\Form\Type\CustomerSelectType",
 *              "grid_name"="customer-customers-select-grid",
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"="commerce"
 *          },
 *          "grid"={
 *              "default"="customer-customers-select-grid",
 *              "context"="customer-customers-context-select-grid"
 *          },
 *          "dataaudit"={
 *              "auditable"=true
 *          }
 *      }
 * )
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 *
 * @method AbstractEnumValue getInternalRating()
 * @method Customer setInternalRating(AbstractEnumValue $enumId)
 */
class Customer implements DatesAwareInterface, ExtendEntityInterface
{
    use DatesAwareTrait;
    use ExtendEntityTrait;

    const INTERNAL_RATING_CODE = 'acc_internal_rating';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ConfigField(
     *     defaultValues={
     *         "importexport"={
     *             "order"=10,
     *             "identity"=-1
     *         }
     *     }
     * )
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "order"=20,
     *              "identity"=-1
     *          }
     *      }
     * )
     */
    protected $name;

    /**
     * @var Customer
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\CustomerBundle\Entity\Customer", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "header"="Parent",
     *              "order"=30
     *          }
     *      }
     * )
     */
    protected $parent;

    /**
     * @var Collection|Customer[]
     *
     * @ORM\OneToMany(targetEntity="Oro\Bundle\CustomerBundle\Entity\Customer", mappedBy="parent")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $children;

    /**
     * @var Collection|CustomerAddress[]
     *
     * @ORM\OneToMany(targetEntity="Oro\Bundle\CustomerBundle\Entity\CustomerAddress",
     *    mappedBy="frontendOwner", cascade={"all"}, orphanRemoval=true
     * )
     * @ORM\OrderBy({"primary" = "DESC"})
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $addresses;

    /**
     * @var CustomerGroup
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\CustomerBundle\Entity\CustomerGroup")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "order"=40
     *          }
     *      }
     * )
     */
    protected $group;

    /**
     * @var Collection|CustomerUser[]
     *
     * @ORM\OneToMany(
     *      targetEntity="Oro\Bundle\CustomerBundle\Entity\CustomerUser",
     *      mappedBy="customer",
     *      cascade={"persist"}
     * )
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     **/
    protected $users;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "order"=50
     *          }
     *      }
     * )
     */
    protected $owner;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $organization;

    /**
     * @var Collection|User[]
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinTable(
     *      name="oro_customer_sales_reps",
     *      joinColumns={
     *          @ORM\JoinColumn(name="customer_id", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     *      }
     * )
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $salesRepresentatives;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.created_at"
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.updated_at"
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $updatedAt;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->addresses = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->salesRepresentatives = new ArrayCollection();
    }

    /**
     * Pre persist event handler
     *
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * Pre update event handler
     *
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getName();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param Customer|null $parent
     *
     * @return $this
     */
    public function setParent(Customer $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Customer
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param AbstractDefaultTypedAddress $address
     *
     * @return $this
     */
    public function addAddress(AbstractDefaultTypedAddress $address)
    {
        if (!$this->getAddresses()->contains($address)) {
            $this->getAddresses()->add($address);
            $address->setFrontendOwner($this);
            $address->setSystemOrganization($this->getOrganization());

            if ($this->getOwner()) {
                $address->setOwner($this->getOwner());
            }
        }

        return $this;
    }

    /**
     * @param AbstractDefaultTypedAddress $address
     *
     * @return $this
     */
    public function removeAddress(AbstractDefaultTypedAddress $address)
    {
        if ($this->hasAddress($address)) {
            $this->getAddresses()->removeElement($address);
        }

        return $this;
    }

    /**
     * Gets one address that has specified type name.
     *
     * @param string $typeName
     *
     * @return AbstractDefaultTypedAddress|null
     */
    public function getAddressByTypeName($typeName)
    {
        /** @var AbstractDefaultTypedAddress $address */
        foreach ($this->getAddresses() as $address) {
            if ($address->hasTypeWithName($typeName)) {
                return $address;
            }
        }

        return null;
    }

    /**
     * Gets primary address if it's available.
     *
     * @return AbstractDefaultTypedAddress|null
     */
    public function getPrimaryAddress()
    {
        /** @var AbstractDefaultTypedAddress $address */
        foreach ($this->getAddresses() as $address) {
            if ($address->isPrimary()) {
                return $address;
            }
        }

        return null;
    }

    /**
     * @return Collection
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @param AbstractDefaultTypedAddress $address
     *
     * @return bool
     */
    protected function hasAddress(AbstractDefaultTypedAddress $address)
    {
        return $this->getAddresses()->contains($address);
    }

    /**
     * @param CustomerGroup|null $group
     *
     * @return $this
     */
    public function setGroup(CustomerGroup $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return CustomerGroup
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param Customer $child
     *
     * @return $this
     */
    public function addChild(Customer $child)
    {
        if (!$this->hasChild($child)) {
            $child->setParent($this);
            $this->children->add($child);
        }

        return $this;
    }

    /**
     * @param Customer $child
     *
     * @return $this
     */
    public function removeChild(Customer $child)
    {
        if ($this->hasChild($child)) {
            $child->setParent(null);
            $this->children->removeElement($child);
        }

        return $this;
    }

    /**
     * @return Collection|Customer[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param Customer $child
     *
     * @return bool
     */
    protected function hasChild(Customer $child)
    {
        return $this->children->contains($child);
    }

    /**
     * @param CustomerUser $customerUser
     *
     * @return $this
     */
    public function addUser(CustomerUser $customerUser)
    {
        if (!$this->hasUser($customerUser)) {
            $customerUser->setCustomer($this);
            if ($this->getOwner()) {
                $customerUser->setOwner($this->getOwner());
            }

            $this->users->add($customerUser);
        }

        return $this;
    }

    /**
     * @param CustomerUser $customerUser
     *
     * @return $this
     */
    public function removeUser(CustomerUser $customerUser)
    {
        if ($this->hasUser($customerUser)) {
            $customerUser->setCustomer(null);
            $this->users->removeElement($customerUser);
        }

        return $this;
    }

    /**
     * @return Collection|CustomerUser[]
     */
    public function getUsers()
    {
        return $this->users;
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
     * @param bool $force
     *
     * @return $this
     */
    public function setOwner(User $owner, $force = true)
    {
        $this->owner = $owner;

        if ($force) {
            foreach ($this->users as $customerUser) {
                $customerUser->setOwner($owner);
            }

            foreach ($this->addresses as $customerAddress) {
                $customerAddress->setOwner($owner);
            }
        }

        return $this;
    }

    /**
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param Organization|null $organization
     *
     * @return $this
     */
    public function setOrganization(Organization $organization = null)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getSalesRepresentatives()
    {
        return $this->salesRepresentatives;
    }

    /**
     * @param User $salesRepresentative
     * @return $this
     */
    public function addSalesRepresentative(User $salesRepresentative)
    {
        if (!$this->salesRepresentatives->contains($salesRepresentative)) {
            $this->salesRepresentatives->add($salesRepresentative);
        }

        return $this;
    }

    /**
     * @param User $salesRepresentative
     * @return $this
     */
    public function removeSalesRepresentative(User $salesRepresentative)
    {
        if ($this->salesRepresentatives->contains($salesRepresentative)) {
            $this->salesRepresentatives->removeElement($salesRepresentative);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function hasSalesRepresentatives()
    {
        return $this->salesRepresentatives->count() > 0;
    }

    /**
     * @param CustomerUser $customerUser
     *
     * @return bool
     */
    protected function hasUser(CustomerUser $customerUser)
    {
        return $this->users->contains($customerUser);
    }
}
