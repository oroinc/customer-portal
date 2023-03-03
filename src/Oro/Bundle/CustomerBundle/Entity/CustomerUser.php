<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EmailBundle\Entity\EmailInterface;
use Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface;
use Oro\Bundle\EmailBundle\Model\EmailHolderInterface;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\AbstractUser;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Security\AdvancedApiUserInterface;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

/**
 * The entity that represents a person who acts on behalf of the company
 * to buy products using OroCommerce store frontend.
 *
 * @ORM\Entity(repositoryClass="Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRepository")
 * @ORM\Table(
 *     name="oro_customer_user",
 *     indexes={
 *         @ORM\Index(name="idx_oro_customer_user_email", columns={"email"}),
 *         @ORM\Index(name="idx_oro_customer_user_email_lowercase", columns={"email_lowercase"}),
 *     }
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *      routeName="oro_customer_customer_user_index",
 *      routeView="oro_customer_customer_user_view",
 *      routeUpdate="oro_customer_customer_user_update",
 *      defaultValues={
 *          "entity"={
 *              "icon"="fa-user",
 *              "contact_information"={
 *                  "email"={
 *                      {"fieldName"="contactInformation"}
 *                  }
 *              }
 *          },
 *          "ownership"={
 *              "owner_type"="USER",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="owner_id",
 *              "frontend_owner_type"="FRONTEND_CUSTOMER",
 *              "frontend_owner_field_name"="customer",
 *              "frontend_owner_column_name"="customer_id",
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id"
 *          },
 *          "form"={
 *              "form_type"="Oro\Bundle\CustomerBundle\Form\Type\CustomerUserSelectType",
 *              "grid_name"="customer-customer-user-select-grid"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"="commerce"
 *          },
 *          "dataaudit"={
 *              "auditable"=true
 *          },
 *          "grid"={
 *              "context"="customer-customer-user-select-grid"
 *          }
 *      }
 * )
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
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

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "order"=1
     *          }
     *      }
     * )
     */
    protected $id;

    /**
     * @var CustomerUserRole[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\CustomerBundle\Entity\CustomerUserRole", inversedBy="customerUsers")
     * @ORM\JoinTable(
     *      name="oro_cus_user_access_role",
     *      joinColumns={
     *          @ORM\JoinColumn(name="customer_user_id", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="customer_user_role_id", referencedColumnName="id", onDelete="CASCADE")
     *      }
     * )
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.customer.customeruser.roles.label",
     *              "description"="oro.customer.customeruser.roles.description"
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "order"=45
     *          }
     *      }
     * )
     */
    protected $userRoles;

    /**
     * @var Customer
     *
     * @ORM\ManyToOne(
     *      targetEntity="Oro\Bundle\CustomerBundle\Entity\Customer",
     *      inversedBy="users",
     *      cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", onDelete="SET NULL")
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
    protected $customer;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "order"=60
     *          }
     *      }
     * )
     */
    protected $confirmed = true;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "identity"=true,
     *              "order"=30
     *          }
     *      }
     * )
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(name="email_lowercase", type="string", length=255)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=false
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      },
     *      mode="hidden"
     * )
     */
    protected $emailLowercase;

    /**
     * Name prefix
     *
     * @var string
     *
     * @ORM\Column(name="name_prefix", type="string", length=255, nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "order"=5
     *          }
     *      }
     * )
     */
    protected $namePrefix;

    /**
     * First name
     *
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "order"=10
     *          }
     *      }
     * )
     */
    protected $firstName;

    /**
     * Middle name
     *
     * @var string
     *
     * @ORM\Column(name="middle_name", type="string", length=255, nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "order"=15
     *          }
     *      }
     * )
     */
    protected $middleName;

    /**
     * Last name
     *
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "order"=20,
     *          }
     *      }
     * )
     */
    protected $lastName;

    /**
     * Name suffix
     *
     * @var string
     *
     * @ORM\Column(name="name_suffix", type="string", length=255, nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "order"=25
     *          }
     *      }
     * )
     */
    protected $nameSuffix;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="birthday", type="date", nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "order"=27
     *          }
     *      }
     * )
     */
    protected $birthday;

    /**
     * @var Collection|CustomerUserAddress[]
     *
     * @ORM\OneToMany(
     *      targetEntity="Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress",
     *      mappedBy="frontendOwner",
     *      cascade={"all"},
     *      orphanRemoval=true
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
     *              "order"=80
     *          }
     *      }
     * )
     */
    protected $owner;

    /**
     * @var CustomerUserApi[]|Collection
     *
     * @ORM\OneToMany(
     *     targetEntity="CustomerUserApi",
     *     mappedBy="user",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true,
     *     fetch="EXTRA_LAZY"
     * )
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          },
     *          "email"={
     *              "available_in_template"=false
     *          }
     *      }
     * )
     */
    protected $apiKeys;

    /**
     * @var Collection|User[]
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinTable(
     *      name="oro_customer_user_sales_reps",
     *      joinColumns={
     *          @ORM\JoinColumn(name="customer_user_id", referencedColumnName="id", onDelete="CASCADE")
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
     * @var \DateTime $createdAt
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $createdAt;

    /**
     * @var \DateTime $updatedAt
     *
     * @ORM\Column(name="updated_at", type="datetime")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $updatedAt;

    /**
     * @var ArrayCollection|CustomerUserSettings[]
     *
     * @ORM\OneToMany(
     *      targetEntity="Oro\Bundle\CustomerBundle\Entity\CustomerUserSettings",
     *      mappedBy="customerUser",
     *      cascade={"all"},
     *      orphanRemoval=true
     * )
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $settings;

    /**
     * @var Website
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\WebsiteBundle\Entity\Website")
     * @ORM\JoinColumn(name="website_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $website;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
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
    protected $enabled = true;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $organization;

    /**
     * @var int
     *
     * @ORM\Column(name="login_count", type="integer", options={"default"=0, "unsigned"=true})
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $loginCount;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $username;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_guest", type="boolean", options={"default"=false})
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "order"=50
     *          }
     *      }
     * )
     */
    protected $isGuest = false;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->addresses = new ArrayCollection();
        $this->salesRepresentatives = new ArrayCollection();
        $this->settings = new ArrayCollection();
        $this->apiKeys = new ArrayCollection();
        parent::__construct();
    }

    public function serialize()
    {
        return $this->__serialize();
    }

    public function unserialize(string $data)
    {
        $this->__unserialize(unserialize($data));
    }

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

    /**
     * {@inheritdoc}
     */
    public function getOwner(): ?User
    {
        return $this->owner;
    }

    /**
     * @param User $owner
     *
     * @return CustomerUser
     */
    public function setOwner(User $owner)
    {
        $this->owner = $owner;

        foreach ($this->addresses as $customerUserAddress) {
            $customerUserAddress->setOwner($owner);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
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
     * Pre persist event listener
     *
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->loginCount = 0;

        $this->createCustomer();
    }

    /**
     * Invoked before the entity is updated.
     *
     * @ORM\PreUpdate
     */
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

    /** {@inheritdoc} */
    public function getEmailFields()
    {
        return ['email'];
    }

    /**
     * {@inheritdoc}
     */
    public function getEmailField()
    {
        return 'email';
    }

    /**
     * {@inheritdoc}
     */
    public function getEmailOwner()
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
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
