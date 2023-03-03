<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;

/**
 * Customer User address entity.
 *
 * @ORM\Table("oro_customer_user_address")
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *       defaultValues={
 *          "entity"={
 *              "icon"="fa-map-marker"
 *          },
 *          "activity"={
 *              "immutable"=true
 *          },
 *          "attachment"={
 *              "immutable"=true
 *          },
 *          "ownership"={
 *              "owner_type"="USER",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="owner_id",
 *              "frontend_owner_type"="FRONTEND_USER",
 *              "frontend_owner_field_name"="frontendOwner",
 *              "frontend_owner_column_name"="frontend_owner_id",
 *              "organization_field_name"="systemOrganization",
 *              "organization_column_name"="system_org_id"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"="commerce"
 *          }
 *      }
 * )
 * @ORM\Entity(repositoryClass="Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserAddressRepository")
 */
class CustomerUserAddress extends AbstractDefaultTypedAddress implements
    AddressPhoneAwareInterface,
    ExtendEntityInterface
{
    use ExtendEntityTrait;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "header"="Address ID"
     *          }
     *      }
     * )
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="Oro\Bundle\CustomerBundle\Entity\CustomerUser",
     *      inversedBy="addresses"
     * )
     * @ORM\JoinColumn(name="frontend_owner_id", referencedColumnName="id", onDelete="CASCADE")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "header"="Customer User",
     *              "identity"=true
     *          }
     *      }
     * )
     */
    protected $frontendOwner;

    /**
     * @var Collection|CustomerUserAddressToAddressType[]
     *
     * @ORM\OneToMany(
     *      targetEntity="Oro\Bundle\CustomerBundle\Entity\CustomerUserAddressToAddressType",
     *      mappedBy="address",
     *      cascade={"persist", "remove", "detach", "refresh"},
     *      orphanRemoval=true
     * )
     **/
    protected $types;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     * @ConfigField(
     *  defaultValues={
     *      "entity"={
     *          "contact_information"="phone"
     *      }
     *  }
     * )
     */
    protected $phone;

    public function __construct()
    {
        parent::__construct();

        $this->types = new ArrayCollection();
    }

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_primary", type="boolean", nullable=true)
     */
    protected $primary;

    /**
     * {@inheritdoc}
     */
    protected function createAddressToAddressTypeEntity()
    {
        return new CustomerUserAddressToAddressType();
    }

    /**
     * Set phone number
     *
     * @param string $phone
     *
     * @return CustomerUserAddress
     */
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
    public function getPhone()
    {
        return $this->phone;
    }
}
