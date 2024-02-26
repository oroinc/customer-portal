<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;

/**
 * Customer User Address To Address Type Doctrine entity
 */
#[ORM\Entity]
#[ORM\Table('oro_cus_usr_adr_to_adr_type')]
#[ORM\UniqueConstraint(
    name: 'oro_customer_user_adr_id_type_name_idx',
    columns: ['customer_user_address_id', 'type_name']
)]
#[Config(mode: 'hidden')]
class CustomerUserAddressToAddressType extends AbstractAddressToAddressType
{
    /**
     * @var CustomerUserAddress|null
     */
    #[ORM\ManyToOne(targetEntity: CustomerUserAddress::class, inversedBy: 'types')]
    #[ORM\JoinColumn(name: 'customer_user_address_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $address;
}
