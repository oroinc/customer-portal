<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ApiFrontend\RestJsonApi;

use Oro\Bundle\CustomerBundle\Tests\Functional\ApiFrontend\UnauthenticatedEnabledTestTrait;

/**
 * The test case to test that 401 response are always returned for unauthenticated requests
 * when using API without authentication is enabled but an API resource marked as required an authenticated user.
 */
class NotAccessibleResourceForUnauthenticatedEnabledTest extends NotAccessibleResourceForUnauthenticatedTest
{
    use UnauthenticatedEnabledTestTrait;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->initializeVisitor();
    }
}
