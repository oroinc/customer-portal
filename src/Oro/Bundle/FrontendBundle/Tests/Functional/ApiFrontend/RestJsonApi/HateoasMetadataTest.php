<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\ApiFrontend\RestJsonApi;

use Oro\Bundle\ApiBundle\Request\ApiAction;
use Oro\Bundle\ApiBundle\Tests\Functional\CheckSkippedEntityTrait;
use Oro\Bundle\ApiBundle\Tests\Functional\HateoasMetadataTrait;
use Oro\Bundle\FrontendBundle\Tests\Functional\ApiFrontend\FrontendRestJsonApiTestCase;

class HateoasMetadataTest extends FrontendRestJsonApiTestCase
{
    use HateoasMetadataTrait;
    use CheckSkippedEntityTrait;

    public function testHateoasMetadataForGetListAction(): void
    {
        $this->runForEntities(function (string $entityClass, array $excludedActions) {
            if (\in_array(ApiAction::GET_LIST, $excludedActions, true)) {
                return;
            }

            if ($this->isSkippedEntity($entityClass, 'get_metadata')) {
                return;
            }

            $metadata = $this->getMetadata($entityClass, ApiAction::GET_LIST);
            if (null === $metadata) {
                return;
            }

            $this->assertHateoasLinksForGetListAction($metadata, $entityClass, $excludedActions);
        });
    }

    public function testHateoasMetadataForGetAction(): void
    {
        $this->runForEntities(function (string $entityClass, array $excludedActions) {
            if (\in_array(ApiAction::GET, $excludedActions, true)) {
                return;
            }

            if ($this->isSkippedEntity($entityClass, 'get_metadata')) {
                return;
            }

            $metadata = $this->getMetadata($entityClass, ApiAction::GET);
            if (null === $metadata) {
                return;
            }

            $this->assertHateoasLinksForGetAction($metadata, $entityClass);
        });
    }
}
