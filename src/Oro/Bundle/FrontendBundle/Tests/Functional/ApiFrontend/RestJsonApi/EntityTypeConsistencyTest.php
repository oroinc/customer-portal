<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\ApiFrontend\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\EntityTypeConsistencyTestTrait;
use Oro\Bundle\FrontendBundle\Tests\Functional\ApiFrontend\FrontendRestJsonApiTestCase;

class EntityTypeConsistencyTest extends FrontendRestJsonApiTestCase
{
    use EntityTypeConsistencyTestTrait;

    public function testEntityTypeConsistency()
    {
        $this->checkEntityTypeConsistency();
    }
}
