<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\ApiFrontend\RestJsonApi;

use Oro\Bundle\CustomerBundle\Tests\Functional\ApiFrontend\UnauthenticatedEnabledTestTrait;

class AddressTypeForUnauthenticatedEnabledTest extends AddressTypeForUnauthenticatedTest
{
    use UnauthenticatedEnabledTestTrait;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->initializeVisitor();
    }
}
