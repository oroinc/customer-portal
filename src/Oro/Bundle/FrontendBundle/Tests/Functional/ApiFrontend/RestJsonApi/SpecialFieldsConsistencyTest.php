<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\ApiFrontend\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\SpecialFieldsConsistencyTestTrait;
use Oro\Bundle\FrontendBundle\Tests\Functional\ApiFrontend\FrontendRestJsonApiTestCase;

class SpecialFieldsConsistencyTest extends FrontendRestJsonApiTestCase
{
    use SpecialFieldsConsistencyTestTrait;

    public function testSpecialFieldsConsistency()
    {
        $this->checkSpecialFieldsConsistency();
    }
}
