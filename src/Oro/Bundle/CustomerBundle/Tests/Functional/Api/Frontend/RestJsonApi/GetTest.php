<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\RestJsonApi;

use Oro\Bundle\ApiBundle\Request\ApiActions;
use Oro\Bundle\ApiBundle\Request\JsonApi\JsonApiDocumentBuilder as JsonApiDoc;
use Oro\Bundle\CustomerBundle\Tests\Functional\Api\DataFixtures\LoadFrontendApiCustomerUserData;
use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;

/**
 * Tests that the access to all registered frontend API resources is granted
 * for the customer user with administrative permissions.
 */
class GetTest extends FrontendRestJsonApiTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadFixtures([LoadFrontendApiCustomerUserData::class]);
    }

    /**
     * @param string   $entityClass
     * @param string[] $excludedActions
     *
     * @dataProvider getEntities
     */
    public function testRestRequests($entityClass, $excludedActions)
    {
        if (in_array(ApiActions::GET_LIST, $excludedActions, true)) {
            return;
        }

        $entityType = $this->getEntityType($entityClass);

        // test "get list" request
        $response = $this->cget(['entity' => $entityType, 'page[size]' => 1], [], [], false);
        self::assertApiResponseStatusCodeEquals($response, 200, $entityType, ApiActions::GET_LIST);
        self::assertResponseContentTypeEquals($response, self::JSON_API_CONTENT_TYPE);

        $id = $this->getFirstEntityId(self::jsonToArray($response->getContent()));
        if (null !== $id) {
            // test "get" request
            if (!in_array(ApiActions::GET, $excludedActions, true)) {
                $response = $this->get(['entity' => $entityType, 'id' => $id], [], [], false);
                self::assertApiResponseStatusCodeEquals($response, 200, $entityType, ApiActions::GET);
                self::assertResponseContentTypeEquals($response, self::JSON_API_CONTENT_TYPE);
            }
        }
    }

    /**
     * @param array $content
     *
     * @return mixed
     */
    protected function getFirstEntityId($content)
    {
        return array_key_exists(JsonApiDoc::DATA, $content) && count($content[JsonApiDoc::DATA]) === 1
            ? $content[JsonApiDoc::DATA][0][JsonApiDoc::ID]
            : null;
    }
}
