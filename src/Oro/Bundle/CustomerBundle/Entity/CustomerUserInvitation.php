<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroCustomerBundle_Entity_CustomerUserInvitation;
use Oro\Bundle\EmailBundle\Model\EmailHolderInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\ConfigField;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOptionInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * Stores an invitation to create a customer user account.
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @method EnumOptionInterface|null getStatus()
 * @method CustomerUserInvitation setStatus(EnumOptionInterface $status)
 * @mixin OroCustomerBundle_Entity_CustomerUserInvitation
 */
#[ORM\Entity]
#[ORM\Table(name: 'oro_customer_user_invitation')]
#[ORM\Index(
    name: 'oro_cus_usr_inv_org_email_idx',
    columns: ['organization_id', 'email']
)]
#[ORM\Index(
    name: 'oro_cus_usr_inv_org_eml_lc_idx',
    columns: ['organization_id', 'email_lowercase']
)]
#[ORM\UniqueConstraint(name: 'oro_cus_usr_inv_token_uidx', columns: ['token_hash'])]
#[ORM\Index(name: 'oro_cus_usr_inv_customer_idx', columns: ['customer_id'])]
#[ORM\HasLifecycleCallbacks]
#[Config(
    routeName: 'oro_customer_customer_user_invitation_index',
    routeCreate: 'oro_customer_customer_user_invitation_create',
    defaultValues: [
        'entity' => ['icon' => 'fa-envelope-o'],
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
        'email' => ['available_in_template' => true]
    ]
)]
class CustomerUserInvitation implements
    DatesAwareInterface,
    EmailHolderInterface,
    OrganizationAwareInterface,
    ExtendEntityInterface
{
    use DatesAwareTrait;
    use ExtendEntityTrait;

    public const INTERNAL_STATUS_CODE = 'cu_invitation_status';
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REVOKED = 'revoked';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Organization::class)]
    #[ORM\JoinColumn(name: 'organization_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?OrganizationInterface $organization = null;

    #[ORM\ManyToOne(targetEntity: Website::class)]
    #[ORM\JoinColumn(name: 'website_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ConfigField(defaultValues: ['email' => ['available_in_template' => true]])]
    private ?Website $website = null;

    #[ORM\ManyToOne(targetEntity: Customer::class)]
    #[ORM\JoinColumn(name: 'customer_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'email' => ['available_in_template' => true]])]
    private ?Customer $customer = null;

    #[ORM\ManyToOne(targetEntity: CustomerUser::class)]
    #[ORM\JoinColumn(name: 'invited_by_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'email' => ['available_in_template' => true]])]
    private ?CustomerUser $invitedBy = null;

    #[ORM\ManyToOne(targetEntity: CustomerUser::class)]
    #[ORM\JoinColumn(name: 'accepted_user_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'email' => ['available_in_template' => true]])]
    private ?CustomerUser $acceptedUser = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'email' => ['available_in_template' => true]])]
    private ?string $email = null;

    #[ORM\Column(name: 'email_lowercase', type: Types::STRING, length: 255)]
    private ?string $emailLowercase = null;

    #[ORM\Column(name: 'token_hash', type: Types::STRING, length: 64, nullable: true)]
    #[ConfigField(defaultValues: [
        'dataaudit' => ['auditable' => false],
        'email' => ['available_in_template' => false, 'immutable' => true]
    ])]
    private ?string $tokenHash = null;

    /** @var Collection<int, CustomerUserRole> */
    #[ORM\ManyToMany(targetEntity: CustomerUserRole::class)]
    #[ORM\JoinTable(name: 'oro_cus_usr_inv_role')]
    #[ORM\JoinColumn(name: 'invitation_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'role_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'email' => ['available_in_template' => true]])]
    private Collection $roles;

    #[ORM\Column(name: 'expires_at', type: Types::DATETIME_MUTABLE)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'email' => ['available_in_template' => true]])]
    private ?\DateTimeInterface $expiresAt = null;

    #[ORM\Column(name: 'sent_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'email' => ['available_in_template' => true]])]
    private ?\DateTimeInterface $sentAt = null;

    #[ORM\Column(name: 'accepted_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'email' => ['available_in_template' => true]])]
    private ?\DateTimeInterface $acceptedAt = null;

    #[ORM\Column(name: 'revoked_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'email' => ['available_in_template' => true]])]
    private ?\DateTimeInterface $revokedAt = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    #[ConfigField(defaultValues: ['email' => ['available_in_template' => true]])]
    protected ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_MUTABLE)]
    #[ConfigField(defaultValues: ['email' => ['available_in_template' => true]])]
    protected ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    #[\Override]
    public function getOrganization(): ?OrganizationInterface
    {
        return $this->organization;
    }

    #[\Override]
    public function setOrganization(?OrganizationInterface $organization = null): static
    {
        $this->organization = $organization;

        return $this;
    }

    public function getWebsite(): ?Website
    {
        return $this->website;
    }

    public function setWebsite(?Website $website): static
    {
        $this->website = $website;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    public function getInvitedBy(): ?CustomerUser
    {
        return $this->invitedBy;
    }

    public function setInvitedBy(?CustomerUser $invitedBy): static
    {
        $this->invitedBy = $invitedBy;

        return $this;
    }

    public function getAcceptedUser(): ?CustomerUser
    {
        return $this->acceptedUser;
    }

    public function setAcceptedUser(?CustomerUser $acceptedUser): static
    {
        $this->acceptedUser = $acceptedUser;

        return $this;
    }

    #[\Override]
    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email !== null ? trim($email) : null;
        $this->emailLowercase = $this->email ? mb_strtolower($this->email) : $this->email;

        return $this;
    }

    public function getEmailLowercase(): ?string
    {
        return $this->emailLowercase;
    }

    public function getTokenHash(): ?string
    {
        return $this->tokenHash;
    }

    public function setTokenHash(?string $tokenHash): static
    {
        $this->tokenHash = $tokenHash;

        return $this;
    }

    /** @return Collection<int, CustomerUserRole> */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    /** @param iterable<CustomerUserRole> $roles */
    public function setRoles(iterable $roles): static
    {
        $this->roles->clear();
        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    public function addRole(CustomerUserRole $role): static
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }

        return $this;
    }

    public function removeRole(CustomerUserRole $role): static
    {
        $this->roles->removeElement($role);

        return $this;
    }

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeInterface $expiresAt): static
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getSentAt(): ?\DateTimeInterface
    {
        return $this->sentAt;
    }

    public function setSentAt(?\DateTimeInterface $sentAt): static
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    public function getAcceptedAt(): ?\DateTimeInterface
    {
        return $this->acceptedAt;
    }

    public function setAcceptedAt(?\DateTimeInterface $acceptedAt): static
    {
        $this->acceptedAt = $acceptedAt;

        return $this;
    }

    public function getRevokedAt(): ?\DateTimeInterface
    {
        return $this->revokedAt;
    }

    public function setRevokedAt(?\DateTimeInterface $revokedAt): static
    {
        $this->revokedAt = $revokedAt;

        return $this;
    }

    public function isPending(): bool
    {
        return $this->getStatus()?->getInternalId() === self::STATUS_PENDING;
    }

    public function isExpired(?\DateTimeInterface $now = null): bool
    {
        $now ??= new \DateTime('now', new \DateTimeZone('UTC'));

        return $this->expiresAt !== null && $this->expiresAt <= $now;
    }

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->createdAt ??= $now;
        $this->updatedAt ??= $now;
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }
}
