<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\ApiFrontend\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\ResourceConsistencyTestTrait;
use Oro\Bundle\FrontendBundle\Tests\Functional\ApiFrontend\FrontendRestJsonApiTestCase;

class ResourceConsistencyTest extends FrontendRestJsonApiTestCase
{
    use ResourceConsistencyTestTrait;

    public function testResourceConsistency()
    {
        $this->runForEntities(function (string $entityClass, array $excludedActions) {
            $this->checkResourceConsistency($entityClass, $excludedActions);
        });
    }
}
