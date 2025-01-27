<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\Tests\Unit\Stub;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressValidationBundle\Model\AddressValidatedAtAwareInterface;
use Oro\Bundle\AddressValidationBundle\Model\AddressValidatedAtAwareTrait;

class AddressValidatedAtAwareStub extends AbstractAddress implements AddressValidatedAtAwareInterface
{
    use AddressValidatedAtAwareTrait;
}
