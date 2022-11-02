<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\SpecialFieldsConsistencyTestTrait;
use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;

class SpecialFieldsConsistencyTest extends FrontendRestJsonApiTestCase
{
    use SpecialFieldsConsistencyTestTrait;

    public function testSpecialFieldsConsistency()
    {
        $this->checkSpecialFieldsConsistency();
    }
}
