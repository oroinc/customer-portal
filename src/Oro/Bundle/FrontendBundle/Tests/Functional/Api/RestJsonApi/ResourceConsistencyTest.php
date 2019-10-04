<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\ResourceConsistencyTestTrait;
use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;

class ResourceConsistencyTest extends FrontendRestJsonApiTestCase
{
    use ResourceConsistencyTestTrait;

    /**
     * @param string   $entityClass
     * @param string[] $excludedActions
     *
     * @dataProvider getEntities
     */
    public function testResourceConsistency($entityClass, $excludedActions)
    {
        $this->checkResourceConsistency($entityClass, $excludedActions);
    }
}
