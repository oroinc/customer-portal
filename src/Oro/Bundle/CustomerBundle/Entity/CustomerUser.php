<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroCustomerBundle_Entity_CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRepository;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserSelectType;
use Oro\Bundle\EmailBundle\Entity\EmailInterface;
use Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface;
use Oro\Bundle\EmailBundle\Model\EmailHolderInterface;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\ConfigField;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Oro\Bundle\UserBundle\Entity\AbstractUser;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Security\AdvancedApiUserInterface;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

/**
 * The entity that represents a person who acts on behalf of the company
 * to buy products using OroCommerce store frontend.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @mixin OroCustomerBundle_Entity_CustomerUser
 */
#[ORM\Entity(repositoryClass: CustomerUserRepository::class)]
#[ORM\Table(name: 'oro_customer_user')]
#[ORM\Index(columns: ['email'], name: 'idx_oro_customer_user_email')]
#[ORM\Index(columns: ['email_lowercase'], name: 'idx_oro_customer_user_email_lowercase')]
#[ORM\HasLifecycleCallbacks]
#[Config(
    routeName: 'oro_customer_customer_user_index',
    routeView: 'oro_customer_customer_user_view',
    routeUpdate: 'oro_customer_customer_user_update',
    defaultValues: [
        'entity' => [
            'icon' => 'fa-user',
            'contact_information' => ['email' => [['fieldName' => 'contactInformation']]]
        ],
        'ownership' => [
            'owner_type' => 'USER',
            'owner_field_name' => 'owner',
            'owner_column_name' => 'owner_id',
            'frontend_owner_type' => 'FRONTEND_CUSTOMER',
            'frontend_owner_field_name' => 'customer',
            'frontend_owner_column_name' => 'customer_id',
            'organization_field_name' => 'organization',
            'organization_column_name' => 'organization_id'
        ],
        'form' => ['form_type' => CustomerUserSelectType::class, 'grid_name' => 'customer-customer-user-select-grid'],
        'security' => ['type' => 'ACL', 'group_name' => 'commerce'],
        'dataaudit' => ['auditable' => true],
        'grid' => ['context' => 'customer-customer-user-select-grid']
    ]
)]
class CustomerUser extends AbstractUser implements
    CustomerUserInterface,
    EmailHolderInterface,
    EmailOwnerInterface,
    EmailInterface,
    AdvancedApiUserInterface,
    \Serializable,
    ExtendEntityInterface
{
    use ExtendEntityTrait;

    const SECURITY_GROUP = 'commerce';

    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ConfigField(defaultValues: ['importexport' => ['order' => 1]])]
    protected ?int $id = null;

    /**
     * @var Collection<int, CustomerUserRole>
     */
    #[ORM\ManyToMany(targetEntity: CustomerUserRole::class, inversedBy: 'customerUsers')]
    #[ORM\JoinTable(name: 'oro_cus_user_access_role')]
    #[ORM\JoinColumn(name: 'customer_user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'customer_user_role_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ConfigField(
        defaultValues: [
            'entity' => [
                'label' => 'oro.customer.customeruser.roles.label',
                'description' => 'oro.customer.customeruser.roles.description'
            ],
            'dataaudit' => ['auditable' => true],
            'importexport' => ['order' => 45]
        ]
    )]
    protected ?Collection $userRoles = null;

    #[ORM\ManyToOne(targetEntity: Customer::class, cascade: ['persist'], inversedBy: 'users')]
    #[ORM\JoinColumn(name: 'customer_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['order' => 40]])]
    protected ?Customer $customer = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['order' => 60]])]
    protected ?bool $confirmed = true;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[ConfigField(
        defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['identity' => true, 'order' => 30]]
    )]
    protected ?string $email = null;

    #[ORM\Column(name: 'email_lowercase', type: Types::STRING, length: 255)]
    #[ConfigField(
        defaultValues: ['dataaudit' => ['auditable' => false], 'importexport' => ['excluded' => true]],
        mode: 'hidden'
    )]
    protected ?string $emailLowercase = null;

    #[ORM\Column(name: 'name_prefix', type: Types::STRING, length: 255, nullable: true)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['order' => 5]])]
    protected ?string $namePrefix = null;

    #[ORM\Column(name: 'first_name', type: Types::STRING, length: 255, nullable: true)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['order' => 10]])]
    protected ?string $firstName = null;

    #[ORM\Column(name: 'middle_name', type: Types::STRING, length: 255, nullable: true)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['order' => 15]])]
    protected ?string $middleName = null;

    #[ORM\Column(name: 'last_name', type: Types::STRING, length: 255, nullable: true)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['order' => 20]])]
    protected ?string $lastName = null;

    #[ORM\Column(name: 'name_suffix', type: Types::STRING, length: 255, nullable: true)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['order' => 25]])]
    protected ?string $nameSuffix = null;

    #[ORM\Column(name: 'birthday', type: Types::DATE_MUTABLE, nullable: true)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['order' => 27]])]
    protected ?\DateTimeInterface $birthday = null;

    /**
     * @var Collection<int, CustomerUserAddress>
     */
    #[ORM\OneToMany(
        mappedBy: 'frontendOwner',
        targetEntity: CustomerUserAddress::class,
        cascade: ['all'],
        orphanRemoval: true
    )]
    #[ORM\OrderBy(['primary' => Criteria::DESC])]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['excluded' => true]])]
    protected ?Collection $addresses = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['order' => 80]])]
    protected ?User $owner = null;

    /**
     * @var Collection<int, CustomerUserApi>
     */
    #[ORM\OneToMany(
        mappedBy: 'user',
        targetEntity: CustomerUserApi::class,
        cascade: ['persist', 'remove'],
        fetch: 'EXTRA_LAZY',
        orphanRemoval: true
    )]
    #[ConfigField(
        defaultValues: ['importexport' => ['excluded' => true], 'email' => ['available_in_template' => false]]
    )]
    protected ?Collection $apiKeys = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'oro_customer_user_sales_reps')]
    #[ORM\JoinColumn(name: 'customer_user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ConfigField(defaultValues: ['importexport' => ['excluded' => true]])]
    protected ?Collection $salesRepresentatives = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    #[ConfigField(defaultValues: ['importexport' => ['excluded' => true]])]
    protected ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_MUTABLE)]
    #[ConfigField(defaultValues: ['importexport' => ['excluded' => true]])]
    protected ?\DateTimeInterface $updatedAt = null;

    /**
     * @var Collection<int, CustomerUserSettings>
     */
    #[ORM\OneToMany(
        mappedBy: 'customerUser',
        targetEntity: CustomerUserSettings::class,
        cascade: ['all'],
        orphanRemoval: true
    )]
    #[ConfigField(defaultValues: ['importexport' => ['excluded' => true]])]
    protected ?Collection $settings = null;

    #[ORM\ManyToOne(targetEntity: Website::class)]
    #[ORM\JoinColumn(name: 'website_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(defaultValues: ['importexport' => ['excluded' => true]])]
    protected ?Website $website = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['order' => 50]])]
    protected ?bool $enabled = true;

    #[ORM\ManyToOne(targetEntity: Organization::class)]
    #[ORM\JoinColumn(name: 'organization_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(defaultValues: ['importexport' => ['excluded' => true]])]
    protected ?OrganizationInterface $organization = null;

    #[ORM\Column(name: 'login_count', type: Types::INTEGER, options: ['default' => 0, 'unsigned' => true])]
    #[ConfigField(defaultValues: ['importexport' => ['excluded' => true]])]
    protected ?int $loginCount = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[ConfigField(defaultValues: ['importexport' => ['excluded' => true]])]
    protected ?string $username = null;

    #[ORM\Column(name: 'is_guest', type: Types::BOOLEAN, options: ['default' => false])]
    #[ConfigField(defaultValues: ['importexport' => ['order' => 50]])]
    protected ?bool $isGuest = false;

    #[ORM\Column(name: 'last_duplicate_notification_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    #[ConfigField(defaultValues: ['importexport' => ['excluded' => true]])]
    protected ?\DateTimeInterface $lastDuplicateNotificationDate = null;

    public function __construct()
    {
        $this->addresses = new ArrayCollection();
        $this->salesRepresentatives = new ArrayCollection();
        $this->settings = new ArrayCollection();
        $this->apiKeys = new ArrayCollection();
        parent::__construct();
    }

    #[\Override]
    public function serialize()
    {
        return $this->__serialize();
    }

    #[\Override]
    public function unserialize(string $data)
    {
        $this->__unserialize(unserialize($data));
    }

    #[\Override]
    public function __serialize(): array
    {
        return [
            $this->password,
            $this->salt,
            $this->username,
            $this->enabled,
            $this->confirmed,
            $this->confirmationToken,
            $this->id
        ];
    }

    #[\Override]
    public function __unserialize(array $serialized): void
    {
        [
            $this->password,
            $this->salt,
            $this->username,
            $this->enabled,
            $this->confirmed,
            $this->confirmationToken,
            $this->id
        ] = $serialized;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return sprintf('%s %s', $this->getFirstName(), $this->getLastName());
    }

    /**
     * @return Customer|null
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param Customer|null $customer
     * @return $this
     */
    public function setCustomer(Customer $customer = null)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @param string|null $companyName
     *
     * @return CustomerUser
     */
    public function createCustomer($companyName = null)
    {
        if (!$this->customer) {
            $this->customer = new Customer();
            $this->fillCustomer($companyName);
        }

        return $this;
    }

    /**
     * @param string|null $companyName
     *
     * @return CustomerUser
     */
    public function fillCustomer($companyName = null)
    {
        $this->customer->setOrganization($this->organization);
        if (!$companyName) {
            $companyName = sprintf('%s %s', $this->firstName, $this->lastName);
        }
        $this->customer->setName($companyName);
        if ($this->getOwner() && !$this->customer->getOwner()) {
            $this->customer->setOwner($this->getOwner(), false);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isConfirmed()
    {
        return $this->confirmed;
    }

    /**
     * @param bool $confirmed
     *
     * @return AbstractUser
     */
    public function setConfirmed($confirmed)
    {
        $this->confirmed = (bool)$confirmed;

        return $this;
    }

    /**
     * @param string $username
     * @return CustomerUser
     */
    #[\Override]
    public function setUsername($username): self
    {
        parent::setUsername($username);

        $this->email = $username;
        $this->emailLowercase = $this->email
            ? mb_strtolower($this->email)
            : $this->email;

        return $this;
    }

    /**
     * @return string
     */
    #[\Override]
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return CustomerUser
     */
    public function setEmail($email)
    {
        $this->username = $email;
        $this->email = $email;
        $this->emailLowercase = $this->email
            ? mb_strtolower($this->email)
            : $this->email;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmailLowercase()
    {
        return $this->emailLowercase;
    }

    /**
     * @return string
     */
    #[\Override]
    public function getNamePrefix()
    {
        return $this->namePrefix;
    }

    /**
     * @param string $namePrefix
     * @return CustomerUser
     */
    public function setNamePrefix($namePrefix)
    {
        $this->namePrefix = $namePrefix;

        return $this;
    }

    /**
     * @return string
     */
    #[\Override]
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return CustomerUser
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string
     */
    #[\Override]
    public function getMiddleName()
    {
        return $this->middleName;
    }

    /**
     * @param string $middleName
     * @return CustomerUser
     */
    public function setMiddleName($middleName)
    {
        $this->middleName = $middleName;

        return $this;
    }

    /**
     * @return string
     */
    #[\Override]
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return CustomerUser
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string
     */
    #[\Override]
    public function getNameSuffix()
    {
        return $this->nameSuffix;
    }

    /**
     * @param string $nameSuffix
     * @return CustomerUser
     */
    public function setNameSuffix($nameSuffix)
    {
        $this->nameSuffix = $nameSuffix;

        return $this;
    }

    public function getBirthday(): ?\DateTime
    {
        return $this->birthday;
    }

    /**
     * @param \DateTime|null $birthday
     * @return CustomerUser
     */
    public function setBirthday(\DateTime $birthday = null)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * Add addresses
     *
     * @param AbstractDefaultTypedAddress $address
     * @return CustomerUser
     */
    public function addAddress(AbstractDefaultTypedAddress $address)
    {
        /** @var AbstractDefaultTypedAddress $address */
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
     * Remove addresses
     *
     * @param AbstractDefaultTypedAddress $addresses
     * @return CustomerUser
     */
    public function removeAddress(AbstractDefaultTypedAddress $addresses)
    {
        if ($this->hasAddress($addresses)) {
            $this->getAddresses()->removeElement($addresses);
        }

        return $this;
    }

    /**
     * Get addresses
     *
     * @return Collection
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @param AbstractDefaultTypedAddress $address
     * @return bool
     */
    protected function hasAddress(AbstractDefaultTypedAddress $address)
    {
        return $this->getAddresses()->contains($address);
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

    #[\Override]
    public function getOwner(): ?User
    {
        return $this->owner;
    }

    /**
     * @param User $owner
     *
     * @return CustomerUser
     */
    #[\Override]
    public function setOwner(User $owner)
    {
        $this->owner = $owner;

        foreach ($this->addresses as $customerUserAddress) {
            $customerUserAddress->setOwner($owner);
        }

        return $this;
    }

    #[\Override]
    public function getApiKeys()
    {
        return $this->apiKeys;
    }

    /**
     * Adds API key to this customer user.
     *
     * @param CustomerUserApi $apiKey
     *
     * @return CustomerUser
     */
    public function addApiKey(CustomerUserApi $apiKey)
    {
        if (!$this->apiKeys->contains($apiKey)) {
            $this->apiKeys->add($apiKey);
            $apiKey->setUser($this);
        }

        return $this;
    }

    /**
     * Removes API key from this customer user.
     *
     * @param CustomerUserApi $apiKey
     *
     * @return CustomerUser
     */
    public function removeApiKey(CustomerUserApi $apiKey)
    {
        if ($this->apiKeys->contains($apiKey)) {
            $this->apiKeys->removeElement($apiKey);
        }

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
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return CustomerUser
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     * @return CustomerUser
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @param Website $website
     * @return null|CustomerUserSettings
     */
    public function getWebsiteSettings(Website $website)
    {
        foreach ($this->settings as $setting) {
            if ($setting->getWebsite()->getId() === $website->getId()) {
                return $setting;
            }
        }

        return null;
    }

    /**
     * @param CustomerUserSettings $websiteSettings
     * @return $this
     */
    public function setWebsiteSettings(CustomerUserSettings $websiteSettings)
    {
        $existing = $this->getWebsiteSettings($websiteSettings->getWebsite());
        if ($existing) {
            $this->settings->removeElement($existing);
        }

        $websiteSettings->setCustomerUser($this);
        $this->settings->add($websiteSettings);

        return $this;
    }

    /**
     * @return Website
     */
    #[\Override]
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @return ArrayCollection|CustomerUserSettings[]
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param Website|null $website
     * @return $this
     */
    #[\Override]
    public function setWebsite(Website $website = null)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * @return bool
     */
    public function isGuest()
    {
        return $this->isGuest;
    }

    /**
     * @param bool $isGuest
     * @return $this
     */
    public function setIsGuest($isGuest)
    {
        $this->isGuest = $isGuest;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getLastDuplicateNotificationDate()
    {
        return $this->lastDuplicateNotificationDate;
    }

    /**
     * @param \DateTimeInterface|null $lastDuplicateNotificationDate
     * @return $this
     */
    public function setLastDuplicateNotificationDate(?\DateTimeInterface $lastDuplicateNotificationDate)
    {
        $this->lastDuplicateNotificationDate = $lastDuplicateNotificationDate;

        return $this;
    }

    /**
     * Pre persist event listener
     */
    #[ORM\PrePersist]
    public function prePersist()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->loginCount = 0;

        $this->createCustomer();
    }

    /**
     * Invoked before the entity is updated.
     */
    #[ORM\PreUpdate]
    public function preUpdate(PreUpdateEventArgs $event)
    {
        $excludedFields = ['lastLogin', 'loginCount'];

        if (array_diff_key($event->getEntityChangeSet(), array_flip($excludedFields))) {
            $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
        }

        if (array_intersect_key($event->getEntityChangeSet(), array_flip(['username', 'email', 'password']))) {
            $this->confirmationToken = null;
            $this->passwordRequestedAt = null;
        }
    }

    #[\Override]
    public function getEmailFields()
    {
        return ['email'];
    }

    #[\Override]
    public function getEmailField()
    {
        return 'email';
    }

    #[\Override]
    public function getEmailOwner()
    {
        return $this;
    }

    #[\Override]
    public function getOrganizations(bool $onlyEnabled = false)
    {
        $organizations = new ArrayCollection();
        if ($this->organization) {
            if (!$onlyEnabled || $this->organization->isEnabled()) {
                $organizations->add($this->organization);
            }
        }

        return $organizations;
    }

    #[\Override]
    public function isEqualTo(SymfonyUserInterface $user): bool
    {
        if (!parent::isEqualTo($user)) {
            return false;
        }

        if ($user instanceof CustomerUser && $this->isConfirmed() !== $user->isConfirmed()) {
            return false;
        }

        return true;
    }
}
