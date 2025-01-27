<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait for the address model aware of {@see CustomerUserAddress};
 */
trait CustomerUserAddressAwareTrait
{
    #[ORM\ManyToOne(targetEntity: CustomerUserAddress::class)]
    #[ORM\JoinColumn(name: 'customer_user_address_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?CustomerUserAddress $customerUserAddress = null;

    public function setCustomerUserAddress(?CustomerUserAddress $customerUserAddress = null): self
    {
        $this->customerUserAddress = $customerUserAddress;

        return $this;
    }

    public function getCustomerUserAddress(): ?CustomerUserAddress
    {
        return $this->customerUserAddress;
    }
}
