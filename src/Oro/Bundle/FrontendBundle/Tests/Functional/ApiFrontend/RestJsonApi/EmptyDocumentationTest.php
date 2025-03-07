<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\ApiFrontend\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\DocumentationTestTrait;
use Oro\Bundle\FrontendBundle\Tests\Functional\ApiFrontend\FrontendRestJsonApiTestCase;

class EmptyDocumentationTest extends FrontendRestJsonApiTestCase
{
    use DocumentationTestTrait;

    /** @var string used in DocumentationTestTrait */
    private const VIEW = 'test_frontend_empty_rest_api';

    public function testDocumentation(): void
    {
        $this->warmUpDocumentationCache();
        $this->assertEmptyDocumentation();
    }
}
