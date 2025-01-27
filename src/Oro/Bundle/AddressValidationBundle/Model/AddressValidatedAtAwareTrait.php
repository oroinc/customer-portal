<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\Model;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Provides the validatedAt property and accessors for entity classes implementing
 * {@see AddressValidatedAtAwareInterface}.
 */
trait AddressValidatedAtAwareTrait
{
    #[ORM\Column(name: 'validated_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?\DateTimeInterface $validatedAt = null;

    public function getValidatedAt(): ?\DateTimeInterface
    {
        return $this->validatedAt;
    }

    public function setValidatedAt(?\DateTimeInterface $validatedAt): self
    {
        $this->validatedAt = $validatedAt;

        return $this;
    }
}
