<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Stub;

use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;

class CustomerVisitorStub extends CustomerVisitor
{
    private ?int $id;

    public function __construct(?int $id = null)
    {
        parent::__construct();

        if ($id !== null) {
            $this->id = $id;
        }
    }
}
