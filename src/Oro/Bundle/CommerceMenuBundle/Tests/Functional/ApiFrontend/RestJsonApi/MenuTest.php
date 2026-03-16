<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Functional\ApiFrontend\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\JsonApiDocContainsConstraint;
use Oro\Bundle\CommerceMenuBundle\Tests\Functional\ApiFrontend\DataFixtures\LoadFrontendMenuContentNodeData;
use Oro\Bundle\CommerceMenuBundle\Tests\Functional\DataFixtures\MenuUpdateWithBrokenItemsData;
use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;
use Oro\Bundle\CustomerBundle\Tests\Functional\ApiFrontend\DataFixtures\LoadAdminCustomerUserData;
use Oro\Bundle\FrontendBundle\Tests\Functional\ApiFrontend\FrontendRestJsonApiTestCase;
use Oro\Bundle\WebCatalogBundle\Tests\Functional\DataFixtures\LoadWebCatalogData;
use Symfony\Component\HttpFoundation\Response;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class MenuTest extends FrontendRestJsonApiTestCase
{
    use ConfigManagerAwareTestTrait;

    private ?int $initialWebCatalogId;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
            LoadAdminCustomerUserData::class,
            LoadWebCatalogData::class,
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
        $response = $this->cget(
            ['entity' => 'menus'],
            ['filter' => ['menu' => 'frontend_menu'], 'include' => 'contentNode.webCatalog']
        );

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
                            'label' => 'Home',
                            'uri' => $this->getUrl('oro_frontend_root'),
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
                                'resourceType' => 'system_page',
                                'apiUrl' => $this->getUrl($this->getItemRouteName(), [
                                    'entity' => 'systempages',
                                    'id' => 'oro_frontend_root'
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
                                    'id' => 'oro_customer_frontend_customer_user_account'
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
                ],
                'included' => [
                    [
                        'type' => 'webcatalogs',
                        'id' => '<toString(@web_catalog.1->id)>',
                        'attributes' => [
                            'name' => 'web_catalog.1',
                            'description' => 'web_catalog.1 description'
                        ]
                    ],
                    [
                        'type' => 'webcatalogtree',
                        'id' => '<toString(@web_catalog.node.1.root->id)>',
                        'attributes' => [
                            'order' => 1,
                            'level' => 0
                        ],
                        'relationships' => [
                            'parent' => ['data' => null],
                            'webCatalog' => [
                                'data' => [
                                    'type' => 'webcatalogs',
                                    'id' => '<toString(@web_catalog.1->id)>'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $response
        );
    }

    public function testGetListWithoutMenuFilter(): void
    {
        $responseWithoutMenuFilter = $this->cget(['entity' => 'menus']);
        $responseWithDefaultMenuFilter = $this->cget(['entity' => 'menus'], ['filter' => ['menu' => 'frontend_menu']]);

        $contentWithoutMenuFilter = self::jsonToArray($responseWithoutMenuFilter->getContent());
        $contentWithDefaultMenuFilter = self::jsonToArray($responseWithDefaultMenuFilter->getContent());
        self::assertCount(\count($contentWithDefaultMenuFilter['data']), $contentWithoutMenuFilter['data']);
    }

    public function testGetListWhenResourceFieldWasNotRequested(): void
    {
        $response = $this->cget(
            ['entity' => 'menus'],
            ['filter' => ['menu' => 'frontend_menu'], 'fields[menus]' => 'label,uri']
        );

        $this->assertMenuResponseContains(
            ['frontend_menu_content_node_item'],
            [
                'data' => [
                    [
                        'type' => 'menus',
                        'id' => 'frontend_menu_content_node_item',
                        'attributes' => [
                            'label' => 'Content Node Menu Item',
                            'uri' => '/web_catalog.node.1.root'
                        ]
                    ]
                ]
            ],
            $response
        );
        $content = self::jsonToArray($response->getContent());
        self::assertCount(2, $content['data'][0]['attributes']);
    }

    public function testGetListWhenOnlyResourceFieldWasRequested(): void
    {
        $response = $this->cget(
            ['entity' => 'menus'],
            ['filter' => ['menu' => 'frontend_menu'], 'fields[menus]' => 'resource']
        );

        $this->assertMenuResponseContains(
            ['frontend_menu_content_node_item'],
            [
                'data' => [
                    [
                        'type' => 'menus',
                        'id' => 'frontend_menu_content_node_item',
                        'attributes' => [
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
                        ]
                    ]
                ]
            ],
            $response
        );
        $content = self::jsonToArray($response->getContent());
        self::assertCount(1, $content['data'][0]['attributes']);
    }

    public function testGetListWithDepthFilter(): void
    {
        $response = $this->cget(
            ['entity' => 'menus'],
            ['filter' => ['menu' => 'frontend_menu', 'depth' => '1']]
        );

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
                            'label' => 'Home'
                        ],
                        'relationships' => [
                            'parent' => ['data' => null]
                        ]
                    ],
                    [
                        'type' => 'menus',
                        'id' => 'frontend_menu_content_node_item',
                        'attributes' => [
                            'label' => 'Content Node Menu Item'
                        ],
                        'relationships' => [
                            'parent' => ['data' => null]
                        ]
                    ]
                ]
            ],
            $response
        );
    }

    public function testGetListForNonExistentItem(): void
    {
        $response = $this->cget(
            ['entity' => 'menus'],
            ['filter' => ['menu' => 'non_existent_menu']]
        );
        self::assertResponseCount(0, $response);
    }

    public function testTryToGetListWithSorting(): void
    {
        $response = $this->cget(
            ['entity' => 'menus'],
            ['filter' => ['menu' => 'frontend_menu'], 'sort' => 'id'],
            [],
            false
        );
        $this->assertResponseValidationError(
            [
                'title' => 'filter constraint',
                'detail' => 'The filter is not supported.',
                'source' => ['parameter' => 'sort']
            ],
            $response
        );
    }

    public function testTryToGetListWithPagination(): void
    {
        $response = $this->cget(
            ['entity' => 'menus'],
            ['filter' => ['menu' => 'frontend_menu'], 'page' => ['number' => 2, 'size' => 3]],
            [],
            false
        );
        $this->assertResponseValidationErrors(
            [
                [
                    'title' => 'filter constraint',
                    'detail' => 'The filter is not supported.',
                    'source' => ['parameter' => 'page[number]']
                ],
                [
                    'title' => 'filter constraint',
                    'detail' => 'The filter is not supported.',
                    'source' => ['parameter' => 'page[size]']
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
