<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\RestJsonApi;

use Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\UnauthenticatedEnabledTestTrait;

/**
 * The test case to test that 401 response are always returned for unauthenticated requests
 * when using API without authentication is enabled but an API resource marked as required an authenticated user.
 */
class NotAccessibleResourceForUnauthenticatedEnabledTest extends NotAccessibleResourceForUnauthenticatedTest
{
    use UnauthenticatedEnabledTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initializeVisitor();
    }
}
