<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroCustomerBundle_Entity_CustomerUserRole;
use Oro\Bundle\ActivityBundle\EntityConfig\ActivityScope;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRoleRepository;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\ConfigField;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Oro\Bundle\UserBundle\Entity\AbstractRole;

/**
 * Entity that represents CustomerUser`s roles in system
 *
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @mixin OroCustomerBundle_Entity_CustomerUserRole
 */
#[ORM\Entity(repositoryClass: CustomerUserRoleRepository::class)]
#[ORM\Table(name: 'oro_customer_user_role')]
#[ORM\UniqueConstraint(name: 'UNIQ_552B533832C8A3DE9395C3F3E', columns: ['organization_id', 'customer_id', 'label'])]
#[Config(
    routeName: 'oro_customer_customer_user_role_index',
    routeCreate: 'oro_customer_customer_user_role_create',
    routeUpdate: 'oro_customer_customer_user_role_update',
    defaultValues: [
        'entity' => ['icon' => 'fa-briefcase'],
        'security' => ['type' => 'ACL', 'group_name' => 'commerce'],
        'ownership' => [
            'owner_type' => 'ORGANIZATION',
            'owner_field_name' => 'organization',
            'owner_column_name' => 'organization_id',
            'frontend_owner_type' => 'FRONTEND_CUSTOMER',
            'frontend_owner_field_name' => 'customer',
            'frontend_owner_column_name' => 'customer_id',
            'organization_field_name' => 'organization',
            'organization_column_name' => 'organization_id'
        ],
        'dataaudit' => ['auditable' => true],
        'activity' => [
            'show_on_page' => ActivityScope::UPDATE_PAGE
        ]
    ]
)]
class CustomerUserRole extends AbstractRole implements OrganizationAwareInterface, ExtendEntityInterface
{
    use ExtendEntityTrait;

    public const PREFIX_ROLE = 'ROLE_FRONTEND_';

    #[ORM\Id]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true, nullable: false)]
    #[ConfigField(defaultValues: ['importexport' => ['identity' => true]])]
    protected ?string $role = null;

    #[ORM\ManyToOne(targetEntity: Customer::class)]
    #[ORM\JoinColumn(name: 'customer_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true]])]
    protected ?Customer $customer = null;

    #[ORM\ManyToOne(targetEntity: Organization::class)]
    #[ORM\JoinColumn(name: 'organization_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true]])]
    protected ?OrganizationInterface $organization = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true]])]
    protected ?string $label = null;

    /**
     * @var Collection<int, CustomerUser>
     */
    #[ORM\ManyToMany(targetEntity: CustomerUser::class, mappedBy: 'userRoles')]
    protected ?Collection $customerUsers = null;

    /**
     * Only self-managed roles should be displayed on the frontend in "Account User Roles" management UI.
     * Account users should not be allowed to see, view or copy the account user roles that are not flagged
     * as "Self-Managed".
     *
     * @var boolean
     */
    #[ORM\Column(name: 'self_managed', type: Types::BOOLEAN, options: ['default' => false])]
    protected ?bool $selfManaged = false;

    #[ORM\Column(name: 'public', type: Types::BOOLEAN, options: ['default' => true])]
    protected ?bool $public = true;

    public function __construct(string $role = '')
    {
        if ($role) {
            $this->setRole($role, false);
        }

        $this->customerUsers = new ArrayCollection();

        parent::__construct($role ? $this->getRole() : '');
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    #[\Override]
    public function getRole()
    {
        return $this->role;
    }

    #[\Override]
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return CustomerUserRole
     */
    public function setLabel($label)
    {
        $this->label = (string)$label;

        return $this;
    }

    #[\Override]
    public function getPrefix()
    {
        return static::PREFIX_ROLE;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param Customer|null $customer
     * @return CustomerUserRole
     */
    public function setCustomer(?Customer $customer = null)
    {
        $this->customer = $customer;

        return $this;
    }

    #[\Override]
    public function getOrganization()
    {
        return $this->organization;
    }

    #[\Override]
    public function setOrganization(?OrganizationInterface $organization = null)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPredefined()
    {
        return !$this->getCustomer();
    }

    public function __clone()
    {
        $this->id = null;
        $this->cloneExtendEntityStorage();
    }

    /**
     * Clones this role and resets some of its properties that should not be shared between this and new role.
     *
     * @return static
     */
    public function duplicate()
    {
        $newRole = clone $this;
        $newRole->setRole($newRole->getLabel());
        $newRole->customerUsers = new ArrayCollection();

        return $newRole;
    }

    /**
     * @param CustomerUser $customerUser
     *
     * @return $this
     */
    public function addCustomerUser(CustomerUser $customerUser)
    {
        if (!$this->customerUsers->contains($customerUser)) {
            $this->customerUsers[] = $customerUser;
        }

        return $this;
    }

    /**
     * @param CustomerUser $customerUser
     *
     * @return $this
     */
    public function removeCustomerUser(CustomerUser $customerUser)
    {
        $this->customerUsers->removeElement($customerUser);

        return $this;
    }

    /**
     * @return Collection|CustomerUser[]
     */
    public function getCustomerUsers()
    {
        return $this->customerUsers;
    }

    /**
     * @return boolean
     */
    public function isSelfManaged()
    {
        return $this->selfManaged;
    }

    /**
     * @param boolean $selfManaged
     * @return $this
     */
    public function setSelfManaged($selfManaged)
    {
        $this->selfManaged = $selfManaged;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isPublic()
    {
        return $this->public;
    }

    /**
     * @param boolean $public
     */
    public function setPublic($public)
    {
        $this->public = $public;
    }

    public function __serialize(): array
    {
        return [
            $this->id,
            $this->role,
            $this->label,
            $this->selfManaged,
            $this->public,
            $this->organization
        ];
    }

    public function __unserialize(array $serialized): void
    {
        [
            $this->id,
            $this->role,
            $this->label,
            $this->selfManaged,
            $this->public,
            $this->organization
        ] = $serialized;

        $this->customerUsers = new ArrayCollection();
    }
}
