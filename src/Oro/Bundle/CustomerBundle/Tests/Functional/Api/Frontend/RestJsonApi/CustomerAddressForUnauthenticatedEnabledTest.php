<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\RestJsonApi;

use Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\UnauthenticatedEnabledTestTrait;

class CustomerAddressForUnauthenticatedEnabledTest extends CustomerAddressForUnauthenticatedTest
{
    use UnauthenticatedEnabledTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initializeVisitor();
    }
}
