<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\ResourceConsistencyTestTrait;
use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;

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
