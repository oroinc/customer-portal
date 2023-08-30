<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Fixtures;

use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitorOwnerAwareInterface;

class CustomerVisitorOwnedEntity implements CustomerVisitorOwnerAwareInterface
{
    public function getVisitor()
    {
        return new CustomerVisitor();
    }
}
