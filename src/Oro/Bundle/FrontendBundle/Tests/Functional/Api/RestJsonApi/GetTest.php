<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\ApiBundle\Request\ApiActions;
use Oro\Bundle\ApiBundle\Request\JsonApi\JsonApiDocumentBuilder as JsonApiDoc;
use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;

/**
 * Tests that all registered frontend API resources are accessible for the anonymous user.
 * Both response status coded, 200 (OK) and 403 (Forbidden), are valid,
 * because the access for some resources can be granted, but for others can be denied for the anonymous user.
 * @group regression
 */
class GetTest extends FrontendRestJsonApiTestCase
{
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
        self::assertApiResponseStatusCodeEquals($response, [200, 403], $entityType, ApiActions::GET_LIST);
        self::assertResponseContentTypeEquals($response, self::JSON_API_CONTENT_TYPE);

        $id = $this->getFirstEntityId(self::jsonToArray($response->getContent()));
        if (null !== $id) {
            // test "get" request
            if (!in_array(ApiActions::GET, $excludedActions, true)) {
                $response = $this->get(['entity' => $entityType, 'id' => $id], [], [], false);
                self::assertApiResponseStatusCodeEquals($response, [200, 403], $entityType, ApiActions::GET);
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
