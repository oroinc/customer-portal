<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\RestJsonApi;

use Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\UnauthenticatedEnabledTestTrait;

class CustomerGroupForUnauthenticatedEnabledTest extends CustomerGroupForUnauthenticatedTest
{
    use UnauthenticatedEnabledTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initializeVisitor();
    }
}
