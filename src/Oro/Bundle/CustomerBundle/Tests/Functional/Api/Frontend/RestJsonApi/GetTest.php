<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\RestJsonApi;

use Oro\Bundle\ApiBundle\Request\ApiAction;
use Oro\Bundle\ApiBundle\Request\JsonApi\JsonApiDocumentBuilder as JsonApiDoc;
use Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\DataFixtures\LoadAdminCustomerUserData;
use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;

/**
 * Tests that the access to all registered frontend API resources is granted
 * for the customer user with administrative permissions.
 * @group regression
 */
class GetTest extends FrontendRestJsonApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([LoadAdminCustomerUserData::class]);
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
            self::assertApiResponseStatusCodeEquals($response, 200, $entityType, ApiAction::GET_LIST);
            self::assertResponseContentTypeEquals($response, self::JSON_API_CONTENT_TYPE);

            $id = $this->getFirstEntityId(self::jsonToArray($response->getContent()));
            if (null !== $id) {
                // test "get" request
                if (!in_array(ApiAction::GET, $excludedActions, true)) {
                    $response = $this->get(['entity' => $entityType, 'id' => $id], [], [], false);
                    self::assertApiResponseStatusCodeEquals($response, 200, $entityType, ApiAction::GET);
                    self::assertResponseContentTypeEquals($response, self::JSON_API_CONTENT_TYPE);
                }
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
