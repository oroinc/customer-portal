<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity\Stub;

use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitorOwnerAwareInterface;

class CustomerVisitorOwnerAwareStub implements CustomerVisitorOwnerAwareInterface
{
    private CustomerVisitor $visitor;

    public function __construct(CustomerVisitor $visitor)
    {
        $this->visitor = $visitor;
    }

    #[\Override]
    public function getVisitor()
    {
        return $this->visitor;
    }
}
