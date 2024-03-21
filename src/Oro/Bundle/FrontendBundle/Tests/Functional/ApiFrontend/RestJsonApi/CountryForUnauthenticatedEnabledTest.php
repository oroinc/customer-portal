<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\ApiFrontend\RestJsonApi;

use Oro\Bundle\CustomerBundle\Tests\Functional\ApiFrontend\UnauthenticatedEnabledTestTrait;

class CountryForUnauthenticatedEnabledTest extends CountryForVisitorTest
{
    use UnauthenticatedEnabledTestTrait;
}
