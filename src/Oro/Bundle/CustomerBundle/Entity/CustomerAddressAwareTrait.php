<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait for the address model aware of {@see CustomerAddress};
 */
trait CustomerAddressAwareTrait
{
    #[ORM\ManyToOne(targetEntity: CustomerAddress::class)]
    #[ORM\JoinColumn(name: 'customer_address_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?CustomerAddress $customerAddress = null;

    public function setCustomerAddress(?CustomerAddress $customerAddress = null): self
    {
        $this->customerAddress = $customerAddress;

        return $this;
    }

    public function getCustomerAddress(): ?CustomerAddress
    {
        return $this->customerAddress;
    }
}
