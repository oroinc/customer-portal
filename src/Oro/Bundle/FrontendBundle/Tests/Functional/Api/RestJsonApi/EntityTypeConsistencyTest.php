<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\EntityTypeConsistencyTestTrait;
use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;

class EntityTypeConsistencyTest extends FrontendRestJsonApiTestCase
{
    use EntityTypeConsistencyTestTrait;

    public function testEntityTypeConsistency()
    {
        $this->checkEntityTypeConsistency();
    }
}
