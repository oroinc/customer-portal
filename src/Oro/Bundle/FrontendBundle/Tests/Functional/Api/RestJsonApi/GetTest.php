<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\ApiBundle\Request\ApiAction;
use Oro\Bundle\ApiBundle\Request\JsonApi\JsonApiDocumentBuilder as JsonApiDoc;
use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;

/**
 * Tests that all registered frontend API resources are accessible for the anonymous user.
 * Status codes 200 (OK), 401 (Unauthorized) and 403 (Forbidden) are valid
 * because the access for some resources can be granted, but for others can be denied for the anonymous user.
 * @group regression
 */
class GetTest extends FrontendRestJsonApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->enableVisitor();
        $this->loadVisitor();
    }

    public function testRestRequests()
    {
        $this->runForEntities(function (string $entityClass, array $excludedActions) {
            if (in_array(ApiAction::GET_LIST, $excludedActions, true)) {
                return;
            }

            $entityType = $this->getEntityType($entityClass);

            // test "get list" request
            $response = $this->cget(['entity' => $entityType], ['page[size]' => 1], [], false);
            if ($response->getStatusCode() === 400) {
                $response = $this->cget(['entity' => $entityType], [], [], false);
            }
            self::assertApiResponseStatusCodeEquals($response, [200, 401, 403], $entityType, ApiAction::GET_LIST);
            if ($response->getStatusCode() !== 401) {
                self::assertResponseContentTypeEquals($response, self::JSON_API_CONTENT_TYPE);
            }

            $id = $this->getFirstEntityId(self::jsonToArray($response->getContent()));
            // test "get" request
            if (null !== $id && !in_array(ApiAction::GET, $excludedActions, true)) {
                $response = $this->get(['entity' => $entityType, 'id' => $id], [], [], false);
                self::assertApiResponseStatusCodeEquals($response, [200, 403], $entityType, ApiAction::GET);
                self::assertResponseContentTypeEquals($response, self::JSON_API_CONTENT_TYPE);
            }
        });
    }

    private function getFirstEntityId(array $content): ?string
    {
        return array_key_exists(JsonApiDoc::DATA, $content) && count($content[JsonApiDoc::DATA]) === 1
            ? $content[JsonApiDoc::DATA][0][JsonApiDoc::ID]
            : null;
    }
}
