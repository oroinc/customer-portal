<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Functional\ApiFrontend\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\JsonApiDocContainsConstraint;
use Oro\Bundle\CommerceMenuBundle\Tests\Functional\ApiFrontend\DataFixtures\LoadFrontendMenuContentNodeData;
use Oro\Bundle\CommerceMenuBundle\Tests\Functional\DataFixtures\MenuUpdateWithBrokenItemsData;
use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;
use Oro\Bundle\CustomerBundle\Tests\Functional\ApiFrontend\DataFixtures\LoadCustomerData;
use Oro\Bundle\FrontendBundle\Tests\Functional\ApiFrontend\FrontendRestJsonApiTestCase;
use Oro\Bundle\WebCatalogBundle\Tests\Functional\DataFixtures\LoadWebCatalogData;
use Symfony\Component\HttpFoundation\Response;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class MenuForVisitorTest extends FrontendRestJsonApiTestCase
{
    use ConfigManagerAwareTestTrait;

    private ?int $initialWebCatalogId;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->initializeVisitor();
        $this->loadFixtures([
            LoadCustomerData::class,
            MenuUpdateWithBrokenItemsData::class,
            LoadFrontendMenuContentNodeData::class
        ]);

        $configManager = self::getConfigManager();
        $this->initialWebCatalogId = $configManager->get('oro_web_catalog.web_catalog');
        $configManager->set(
            'oro_web_catalog.web_catalog',
            $this->getReference(LoadWebCatalogData::CATALOG_1)->getId()
        );
        $configManager->flush();
    }

    #[\Override]
    protected function tearDown(): void
    {
        $configManager = self::getConfigManager();
        $configManager->set('oro_web_catalog.web_catalog', $this->initialWebCatalogId);
        $configManager->flush();
    }

    private function assertMenuResponseContains(array $ids, array $expectedContent, Response $response): void
    {
        $content = self::jsonToArray($response->getContent());
        if (!empty($content['data'])) {
            $content['data'] = array_values(array_filter($content['data'], function (array $item) use ($ids) {
                return in_array($item['id'], $ids, true);
            }));
        }
        $expectedContent = $this->getResponseData($expectedContent);

        self::assertThat($content, new JsonApiDocContainsConstraint($expectedContent, false, false));
    }

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

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetList(): void
    {
        $response = $this->cget(['entity' => 'menus']);

        $this->assertMenuResponseContains(
            [
                'oro_customer_menu_customer_user_index',
                'oro_customer_frontend_customer_user_dashboard',
                'frontend_menu_content_node_item'
            ],
            [
                'data' => [
                    [
                        'type' => 'menus',
                        'id' => 'oro_customer_menu_customer_user_index',
                        'attributes' => [
                            'label' => 'My Account',
                            'uri' => $this->getUrl('oro_customer_frontend_customer_user_profile'),
                            'description' => null,
                            'extras' => [
                                'position' => null,
                                'icon' => null,
                                'image' => null,
                                'screens' => null,
                                'max_traverse_level' => null,
                                'menu_template' => null
                            ],
                            'link_attributes' => [],
                            'resource' => [
                                'isSlug' => false,
                                'redirectUrl' => null,
                                'redirectStatusCode' => null,
                                'resourceType' => 'customer_user',
                                'apiUrl' => $this->getUrl($this->getItemRouteName(), [
                                    'entity' => 'customerusers',
                                    'id' => 'mine'
                                ])
                            ]
                        ],
                        'relationships' => [
                            'contentNode' => ['data' => null],
                            'parent' => ['data' => null]
                        ]
                    ],
                    [
                        'type' => 'menus',
                        'id' => 'oro_customer_frontend_customer_user_dashboard',
                        'attributes' => [
                            'label' => 'Dashboard',
                            'uri' => $this->getUrl('oro_customer_frontend_customer_user_dashboard_index'),
                            'description' => null,
                            'extras' => [
                                'position' => 1,
                                'icon' => null,
                                'image' => null,
                                'screens' => null,
                                'max_traverse_level' => null,
                                'menu_template' => null
                            ],
                            'link_attributes' => [],
                            'resource' => [
                                'isSlug' => false,
                                'redirectUrl' => null,
                                'redirectStatusCode' => null,
                                'resourceType' => 'system_page',
                                'apiUrl' => $this->getUrl($this->getItemRouteName(), [
                                    'entity' => 'systempages',
                                    'id' => 'oro_customer_frontend_customer_user_dashboard_index'
                                ])
                            ]
                        ],
                        'relationships' => [
                            'contentNode' => ['data' => null],
                            'parent' => [
                                'data' => [
                                    'type' => 'menus',
                                    'id' => 'oro_customer_menu_customer_user_index'
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'menus',
                        'id' => 'frontend_menu_content_node_item',
                        'attributes' => [
                            'label' => 'Content Node Menu Item',
                            'uri' => '/web_catalog.node.1.root',
                            'description' => 'Test menu item with content node',
                            'extras' => [
                                'position' => 10,
                                'icon' => null,
                                'image' => null,
                                'screens' => [],
                                'max_traverse_level' => 0,
                                'menu_template' => null
                            ],
                            'link_attributes' => [],
                            'resource' => [
                                'isSlug' => true,
                                'redirectUrl' => null,
                                'redirectStatusCode' => null,
                                'resourceType' => 'system_page',
                                'apiUrl' => $this->getUrl($this->getItemRouteName(), [
                                    'entity' => 'systempages',
                                    'id' => 'oro_frontend_root'
                                ])
                            ]
                        ],
                        'relationships' => [
                            'contentNode' => [
                                'data' => [
                                    'type' => 'webcatalogtree',
                                    'id' => '<toString(@web_catalog.node.1.root->id)>'
                                ]
                            ],
                            'parent' => ['data' => null]
                        ]
                    ]
                ]
            ],
            $response
        );
    }

    public function testTryToGet(): void
    {
        $response = $this->get(
            ['entity' => 'menus', 'id' => 'frontend_menu_content_node_item'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    public function testTryToCreate(): void
    {
        $response = $this->post(
            ['entity' => 'menus'],
            [],
            [],
            false
        );
        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }

    public function testTryToUpdate(): void
    {
        $response = $this->patch(
            ['entity' => 'menus', 'id' => 'frontend_menu_content_node_item'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    public function testTryToDelete(): void
    {
        $response = $this->delete(
            ['entity' => 'menus', 'id' => 'frontend_menu_content_node_item'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    public function testTryToDeleteList(): void
    {
        $response = $this->cdelete(
            ['entity' => 'menus'],
            ['filter' => ['id' => 'frontend_menu_content_node_item']],
            [],
            false
        );
        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }

    public function testTryToGetSubresourceForParent(): void
    {
        $response = $this->getSubresource(
            ['entity' => 'menus', 'id' => 'frontend_menu_content_node_item', 'association' => 'parent'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    public function testTryToGetRelationshipForParent(): void
    {
        $response = $this->getRelationship(
            ['entity' => 'menus', 'id' => 'frontend_menu_content_node_item', 'association' => 'parent'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    public function testTryToUpdateRelationshipForParent(): void
    {
        $response = $this->patchRelationship(
            ['entity' => 'menus', 'id' => 'frontend_menu_content_node_item', 'association' => 'parent'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    public function testTryToGetSubresourceForContentNode(): void
    {
        $response = $this->getSubresource(
            ['entity' => 'menus', 'id' => 'frontend_menu_content_node_item', 'association' => 'contentNode'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    public function testTryToGetRelationshipForContentNode(): void
    {
        $response = $this->getRelationship(
            ['entity' => 'menus', 'id' => 'frontend_menu_content_node_item', 'association' => 'contentNode'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    public function testTryToUpdateRelationshipForContentNode(): void
    {
        $response = $this->patchRelationship(
            ['entity' => 'menus', 'id' => 'frontend_menu_content_node_item', 'association' => 'contentNode'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    public function testTryToAddRelationshipForContentNode(): void
    {
        $response = $this->postRelationship(
            ['entity' => 'menus', 'id' => 'frontend_menu_content_node_item', 'association' => 'contentNode'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    public function testTryToDeleteRelationshipForContentNode(): void
    {
        $response = $this->deleteRelationship(
            ['entity' => 'menus', 'id' => 'frontend_menu_content_node_item', 'association' => 'contentNode'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }
}
