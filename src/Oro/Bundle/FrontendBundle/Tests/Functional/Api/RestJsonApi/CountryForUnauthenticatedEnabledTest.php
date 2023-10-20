<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\UnauthenticatedEnabledTestTrait;

class CountryForUnauthenticatedEnabledTest extends CountryForVisitorTest
{
    use UnauthenticatedEnabledTestTrait;
}
