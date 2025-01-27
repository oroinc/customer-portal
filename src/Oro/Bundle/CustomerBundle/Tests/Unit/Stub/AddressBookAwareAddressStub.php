<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Stub;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\CustomerBundle\Entity\AddressBookAwareInterface;
use Oro\Bundle\CustomerBundle\Entity\AddressBookAwareTrait;

class AddressBookAwareAddressStub extends AbstractAddress implements AddressBookAwareInterface
{
    use AddressBookAwareTrait;
}
