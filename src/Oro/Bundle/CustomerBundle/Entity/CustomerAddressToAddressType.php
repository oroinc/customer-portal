<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;

/**
 * Customer Address To Address Type Doctrine entity
 */
#[ORM\Entity]
#[ORM\Table('oro_customer_adr_adr_type')]
#[ORM\UniqueConstraint(name: 'oro_customer_adr_id_type_name_idx', columns: ['customer_address_id', 'type_name'])]
#[Config(mode: 'hidden')]
class CustomerAddressToAddressType extends AbstractAddressToAddressType
{
    /**
     * @var CustomerAddress|null
     */
    #[ORM\ManyToOne(targetEntity: CustomerAddress::class, inversedBy: 'types')]
    #[ORM\JoinColumn(name: 'customer_address_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $address;
}
