<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Functional\ApiFrontend\RestJsonApi;

use Oro\Bundle\FrontendBundle\Tests\Functional\ApiFrontend\FrontendRestJsonApiTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class MenuForUnauthenticatedTest extends FrontendRestJsonApiTestCase
{
    public function testGetOptionsForList(): void
    {
        $response = $this->options(
            $this->getListRouteName(),
            ['entity' => 'menus']
        );
        self::assertAllowResponseHeader($response, 'OPTIONS, GET');
    }

    public function testTryToOptionsForItem(): void
    {
        $response = $this->options(
            $this->getItemRouteName(),
            ['entity' => 'menus', 'id' => 'frontend_menu_content_node_item'],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    public function testTryToGetList(): void
    {
        $response = $this->cget(
            ['entity' => 'menus'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testTryToGet(): void
    {
        $response = $this->get(
            ['entity' => 'menus', 'id' => 'frontend_menu_content_node_item'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testTryToCreate(): void
    {
        $response = $this->post(
            ['entity' => 'menus'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testTryToUpdate(): void
    {
        $response = $this->patch(
            ['entity' => 'menus', 'id' => 'frontend_menu_content_node_item'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testTryToDelete(): void
    {
        $response = $this->delete(
            ['entity' => 'menus', 'id' => 'frontend_menu_content_node_item'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testTryToDeleteList(): void
    {
        $response = $this->cdelete(
            ['entity' => 'menus'],
            ['filter' => ['id' => 'frontend_menu_content_node_item']],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testTryToGetSubresourceForParent(): void
    {
        $response = $this->getSubresource(
            ['entity' => 'menus', 'id' => 'frontend_menu_content_node_item', 'association' => 'parent'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testTryToGetRelationshipForParent(): void
    {
        $response = $this->getRelationship(
            ['entity' => 'menus', 'id' => 'frontend_menu_content_node_item', 'association' => 'parent'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testTryToUpdateRelationshipForParent(): void
    {
        $response = $this->patchRelationship(
            ['entity' => 'menus', 'id' => 'frontend_menu_content_node_item', 'association' => 'parent'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testTryToGetSubresourceForContentNode(): void
    {
        $response = $this->getSubresource(
            ['entity' => 'menus', 'id' => 'frontend_menu_content_node_item', 'association' => 'contentNode'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testTryToGetRelationshipForContentNode(): void
    {
        $response = $this->getRelationship(
            ['entity' => 'menus', 'id' => 'frontend_menu_content_node_item', 'association' => 'contentNode'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testTryToUpdateRelationshipForContentNode(): void
    {
        $response = $this->patchRelationship(
            ['entity' => 'menus', 'id' => 'frontend_menu_content_node_item', 'association' => 'contentNode'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testTryToAddRelationshipForContentNode(): void
    {
        $response = $this->postRelationship(
            ['entity' => 'menus', 'id' => 'frontend_menu_content_node_item', 'association' => 'contentNode'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testTryToDeleteRelationshipForContentNode(): void
    {
        $response = $this->deleteRelationship(
            ['entity' => 'menus', 'id' => 'frontend_menu_content_node_item', 'association' => 'contentNode'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_UNAUTHORIZED);
    }
}
