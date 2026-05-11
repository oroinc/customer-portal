<?php

namespace Oro\Bundle\CustomerBundle\Model;

use Oro\Bundle\EmailBundle\Model\EmailHolderInterface;

/**
 * Simple email holder.
 */
class EmailHolder implements EmailHolderInterface
{
    public function __construct(
        private string $email,
    ) {
    }

    #[\Override]
    public function getEmail(): string
    {
        return $this->email;
    }
}
