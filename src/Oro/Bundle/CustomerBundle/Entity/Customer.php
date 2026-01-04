<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroCustomerBundle_Entity_Customer;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerRepository;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerSelectType;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\ConfigField;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOptionInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Entity represents Customer and handles all related mappings
 *
 *
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 *
 * @method EnumOptionInterface getInternalRating()
 * @method Customer setInternalRating(EnumOptionInterface $enumId)
 * @mixin OroCustomerBundle_Entity_Customer
 */
#[ORM\Entity(repositoryClass: CustomerRepository::class)]
#[ORM\Table(name: 'oro_customer')]
#[ORM\Index(columns: ['name'], name: 'oro_customer_name_idx')]
#[ORM\Index(columns: ['created_at'], name: 'idx_oro_customer_created_at')]
#[ORM\Index(columns: ['updated_at'], name: 'idx_oro_customer_updated_at')]
#[Config(
    routeName: 'oro_customer_customer_index',
    routeView: 'oro_customer_customer_view',
    routeCreate: 'oro_customer_customer_create',
    routeUpdate: 'oro_customer_customer_update',
    defaultValues: [
        'entity' => ['icon' => 'fa-building'],
        'ownership' => [
            'owner_type' => 'USER',
            'owner_field_name' => 'owner',
            'owner_column_name' => 'owner_id',
            'organization_field_name' => 'organization',
            'organization_column_name' => 'organization_id',
            'frontend_owner_type' => 'FRONTEND_CUSTOMER',
            'frontend_owner_field_name' => 'parent',
            'frontend_owner_column_name' => 'parent_id'
        ],
        'form' => ['form_type' => CustomerSelectType::class, 'grid_name' => 'customer-customers-select-grid'],
        'security' => ['type' => 'ACL', 'group_name' => 'commerce'],
        'grid' => [
            'default' => 'customer-customers-select-grid',
            'context' => 'customer-customers-context-select-grid'
        ],
        'dataaudit' => ['auditable' => true]
    ]
)]
class Customer implements DatesAwareInterface, ExtendEntityInterface
{
    use DatesAwareTrait;
    use ExtendEntityTrait;

    public const INTERNAL_RATING_CODE = 'acc_internal_rating';

    /**
     * @var integer
     *
     */
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ConfigField(defaultValues: ['importexport' => ['order' => 10, 'identity' => -1]])]
    protected $id;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 255)]
    #[ConfigField(
        defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['order' => 20, 'identity' => -1]]
    )]
    protected ?string $name = null;

    #[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(
        defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['header' => 'Parent', 'order' => 30]]
    )]
    protected ?Customer $parent = null;

    /**
     * @var Collection<int, Customer>
     */
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: Customer::class)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['excluded' => true]])]
    protected ?Collection $children = null;

    /**
     * @var Collection<int, CustomerAddress>
     */
    #[ORM\OneToMany(
        mappedBy: 'frontendOwner',
        targetEntity: CustomerAddress::class,
        cascade: ['all'],
        orphanRemoval: true
    )]
    #[ORM\OrderBy(['primary' => Criteria::DESC])]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['excluded' => true]])]
    protected ?Collection $addresses = null;

    #[ORM\ManyToOne(targetEntity: CustomerGroup::class)]
    #[ORM\JoinColumn(name: 'group_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['order' => 40]])]
    protected ?CustomerGroup $group = null;

    /**
     * @var Collection<int, CustomerUser>
     **/
    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: CustomerUser::class, cascade: ['persist'])]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['excluded' => true]])]
    protected ?Collection $users = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['order' => 50]])]
    protected ?User $owner = null;

    #[ORM\ManyToOne(targetEntity: Organization::class)]
    #[ORM\JoinColumn(name: 'organization_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['excluded' => true]])]
    protected ?OrganizationInterface $organization = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'oro_customer_sales_reps')]
    #[ORM\JoinColumn(name: 'customer_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ConfigField(defaultValues: ['importexport' => ['excluded' => true]])]
    protected ?Collection $salesRepresentatives = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    #[ConfigField(
        defaultValues: ['entity' => ['label' => 'oro.ui.created_at'], 'importexport' => ['excluded' => true]]
    )]
    protected ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_MUTABLE)]
    #[ConfigField(
        defaultValues: ['entity' => ['label' => 'oro.ui.updated_at'], 'importexport' => ['excluded' => true]]
    )]
    protected ?\DateTimeInterface $updatedAt = null;

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
     */
    #[ORM\PrePersist]
    public function prePersist()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * Pre update event handler
     */
    #[ORM\PreUpdate]
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @return string
     */
    #[\Override]
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
    public function setParent(?Customer $parent = null)
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
    public function setGroup(?CustomerGroup $group = null)
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
    public function setOrganization(?Organization $organization = null)
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
