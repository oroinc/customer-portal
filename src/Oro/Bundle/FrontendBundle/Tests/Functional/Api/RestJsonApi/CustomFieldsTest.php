<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\Environment\Entity\TestOwner;
use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class CustomFieldsTest extends FrontendRestJsonApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->enableVisitor();
        $this->loadFixtures([
            '@OroApiBundle/Tests/Functional/DataFixtures/custom_fields.yml'
        ]);
    }

    /**
     * @param Response $response
     * @param int      $entityId
     */
    private function assertHasCustomField(Response $response, $entityId)
    {
        $this->assertResponseContains(
            [
                'data' => [
                    'type'       => 'testapiowners',
                    'id'         => (string)$entityId,
                    'attributes' => [
                        'name'               => 'Owner 1',
                        'extend_description' => 'Description for Owner 1'
                    ]
                ]
            ],
            $response
        );
    }

    /**
     * @param Response $response
     * @param int      $entityId
     */
    private function assertNotHasCustomField(Response $response, $entityId)
    {
        $this->assertResponseContains(
            [
                'data' => [
                    'type'       => 'testapiowners',
                    'id'         => (string)$entityId,
                    'attributes' => [
                        'name' => 'Owner 1'
                    ]
                ]
            ],
            $response
        );
        $responseContent = self::jsonToArray($response->getContent());
        self::assertArrayNotHasKey(
            'extend_description',
            $responseContent['data']['attributes']
        );
    }

    public function testCustomFieldsShouldNotBeAddedByDefault()
    {
        $entityId = $this->getReference('owner1')->id;
        $this->getEntityManager()->clear();
        $response = $this->get(
            ['entity' => 'testapiowners', 'id' => (string)$entityId]
        );
        $this->assertNotHasCustomField($response, $entityId);
    }

    public function testShouldBePossibleToAddCustomField()
    {
        $this->appendEntityConfig(
            TestOwner::class,
            [
                'fields' => [
                    'extend_description' => null
                ]
            ]
        );
        $entityId = $this->getReference('owner1')->id;
        $this->getEntityManager()->clear();
        $response = $this->get(
            ['entity' => 'testapiowners', 'id' => (string)$entityId]
        );
        $this->assertHasCustomField($response, $entityId);
    }

    public function testShouldBePossibleToAddAndRenameCustomField()
    {
        $this->appendEntityConfig(
            TestOwner::class,
            [
                'fields' => [
                    'extendDescription' => [
                        'property_path' => 'extend_description'
                    ]
                ]
            ]
        );
        $entityId = $this->getReference('owner1')->id;
        $this->getEntityManager()->clear();
        $response = $this->get(
            ['entity' => 'testapiowners', 'id' => (string)$entityId]
        );
        $this->assertResponseContains(
            [
                'data' => [
                    'type'       => 'testapiowners',
                    'id'         => (string)$entityId,
                    'attributes' => [
                        'name'              => 'Owner 1',
                        'extendDescription' => 'Description for Owner 1'
                    ]
                ]
            ],
            $response
        );
    }

    public function testCustomFieldsShouldBeAddedWhenExclusionPolicyEqualsToNone()
    {
        $this->appendEntityConfig(
            TestOwner::class,
            ['exclusion_policy' => 'none']
        );
        $entityId = $this->getReference('owner1')->id;
        $this->getEntityManager()->clear();
        $response = $this->get(
            ['entity' => 'testapiowners', 'id' => (string)$entityId]
        );
        $this->assertHasCustomField($response, $entityId);
    }

    public function testCustomFieldsShouldNotBeAddedWhenExclusionPolicyEqualsToAll()
    {
        $this->appendEntityConfig(
            TestOwner::class,
            [
                'exclusion_policy' => 'all',
                'fields'           => [
                    'id'   => null,
                    'name' => null
                ]
            ]
        );
        $entityId = $this->getReference('owner1')->id;
        $this->getEntityManager()->clear();
        $response = $this->get(
            ['entity' => 'testapiowners', 'id' => (string)$entityId]
        );
        $this->assertNotHasCustomField($response, $entityId);
    }
}
