<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\CustomerBundle\Model\ExtendCustomerUserAddress;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

/**
 * @ORM\Table("oro_customer_user_address")
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *       defaultValues={
 *          "entity"={
 *              "icon"="fa-map-marker"
 *          },
 *          "note"={
 *              "immutable"=true
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
class CustomerUserAddress extends ExtendCustomerUserAddress implements AddressPhoneAwareInterface
{
    /**
     * @ORM\ManyToOne(
     *      targetEntity="Oro\Bundle\CustomerBundle\Entity\CustomerUser",
     *      inversedBy="addresses",
     *      cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="frontend_owner_id", referencedColumnName="id", onDelete="CASCADE")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
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
     *      cascade={"persist", "remove", "detach"},
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
