<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroCustomerBundle_Entity_CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerAddressRepository;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\ConfigField;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;

/**
 * Customer Address entity.
 *
 * @mixin OroCustomerBundle_Entity_CustomerAddress
 */
#[ORM\Entity(repositoryClass: CustomerAddressRepository::class)]
#[ORM\Table('oro_customer_address')]
#[ORM\HasLifecycleCallbacks]
#[Config(
    defaultValues: [
        'entity' => ['icon' => 'fa-map-marker'],
        'activity' => ['immutable' => true],
        'attachment' => ['immutable' => true],
        'ownership' => [
            'owner_type' => 'USER',
            'owner_field_name' => 'owner',
            'owner_column_name' => 'owner_id',
            'frontend_owner_type' => 'FRONTEND_CUSTOMER',
            'frontend_owner_field_name' => 'frontendOwner',
            'frontend_owner_column_name' => 'frontend_owner_id',
            'organization_field_name' => 'systemOrganization',
            'organization_column_name' => 'system_org_id'
        ],
        'security' => ['type' => 'ACL', 'group_name' => 'commerce']
    ]
)]
class CustomerAddress extends AbstractDefaultTypedAddress implements AddressPhoneAwareInterface, ExtendEntityInterface
{
    use ExtendEntityTrait;

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ConfigField(defaultValues: ['importexport' => ['header' => 'Address ID']])]
    protected ?int $id = null;

    /**
     * @var Customer|null
     */
    #[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: 'addresses')]
    #[ORM\JoinColumn(name: 'frontend_owner_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ConfigField(defaultValues: ['importexport' => ['header' => 'Customer', 'identity' => true]])]
    protected $frontendOwner;

    /**
     * @var Collection<int, CustomerAddressToAddressType>
     */
    #[ORM\OneToMany(
        mappedBy: 'address',
        targetEntity: CustomerAddressToAddressType::class,
        cascade: ['persist', 'remove', 'detach', 'refresh'],
        orphanRemoval: true
    )]
    protected ?Collection $types = null;

    #[ORM\Column(name: 'phone', type: Types::STRING, length: 255, nullable: true)]
    #[ConfigField(defaultValues: ['entity' => ['contact_information' => 'phone']])]
    protected ?string $phone = null;

    #[ORM\Column(name: 'is_primary', type: Types::BOOLEAN, nullable: true)]
    protected ?bool $primary = null;

    public function __construct()
    {
        parent::__construct();

        $this->types = new ArrayCollection();
    }

    #[\Override]
    protected function createAddressToAddressTypeEntity()
    {
        return new CustomerAddressToAddressType();
    }

    #[\Override]
    public function setFrontendOwner($frontendOwner = null)
    {
        if (null === $frontendOwner && null !== $this->frontendOwner) {
            $this->frontendOwner->removeAddress($this);
        }
        parent::setFrontendOwner($frontendOwner);
        if (null !== $this->frontendOwner) {
            $this->frontendOwner->addAddress($this);
        }

        return $this;
    }

    /**
     * Set phone number
     *
     * @param string $phone
     *
     * @return CustomerAddress
     */
    #[\Override]
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone number
     *
     * @return string
     */
    #[\Override]
    public function getPhone()
    {
        return $this->phone;
    }
}
