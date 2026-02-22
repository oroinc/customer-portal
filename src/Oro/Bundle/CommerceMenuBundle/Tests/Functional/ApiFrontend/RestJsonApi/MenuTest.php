<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Functional\ApiFrontend\RestJsonApi;

use Oro\Bundle\CommerceMenuBundle\Tests\Functional\DataFixtures\LoadFrontendMenuContentNodeData;
use Oro\Bundle\CommerceMenuBundle\Tests\Functional\DataFixtures\MenuUpdateWithBrokenItemsData;
use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;
use Oro\Bundle\FrontendBundle\Tests\Functional\ApiFrontend\FrontendRestJsonApiTestCase;
use Oro\Bundle\WebCatalogBundle\Tests\Functional\DataFixtures\LoadWebCatalogData;
use Symfony\Component\HttpFoundation\Response;

/**
 * @dbIsolationPerTest
 */
final class MenuTest extends FrontendRestJsonApiTestCase
{
    use ConfigManagerAwareTestTrait;

    private ?int $initialWebCatalogId;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
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

        $this->initializeVisitor();
    }

    #[\Override]
    protected function tearDown(): void
    {
        $configManager = self::getConfigManager();
        $configManager->set('oro_web_catalog.web_catalog', $this->initialWebCatalogId);
        $configManager->flush();
    }

    public function testGetMenu(): void
    {
        $response = $this->cget(
            ['entity' => 'menus'],
            ['filter' => ['menu' => 'frontend_menu'], 'include' => 'contentNode']
        );

        self::assertResponseStatusCodeEquals($response, Response::HTTP_OK);

        $content = self::jsonToArray($response->getContent());
        self::assertArrayHasKey('data', $content);
        self::assertIsArray($content['data']);
        self::assertNotEmpty($content['data'], 'frontend_menu should have items from MenuUpdate fixtures');

        $contentNodeItem = array_find(
            $content['data'],
            fn ($item) => isset($item['relationships']['contentNode']['data'])
        );

        self::assertNotNull(
            $contentNodeItem,
            'frontend_menu_content_node_item should be present in frontend_menu'
        );

        $contentNodeInIncludes = array_find(
            $content['included'],
            fn ($item) => ($item['type'] ?? null) === 'webcatalogtree'
        );
        self::assertNotNull(
            $contentNodeInIncludes,
            'ContentNode should be included in response'
        );
    }

    public function testGetMenuWithDepth(): void
    {
        $response = $this->cget(
            ['entity' => 'menus'],
            ['filter' => ['menu' => 'test_menu', 'depth' => '1']]
        );

        self::assertResponseStatusCodeEquals($response, Response::HTTP_OK);

        $content = self::jsonToArray($response->getContent());
        self::assertArrayHasKey('data', $content);
        self::assertIsArray($content['data']);

        $itemsWithDepth = $content['data'];

        $itemsWithParent = array_filter(
            $itemsWithDepth,
            fn ($item) => isset($item['relationships']['parent']['data'])
        );
        self::assertEmpty(
            $itemsWithParent,
            sprintf(
                'All items should have no parent when depth=1, but found %d items with parent',
                count($itemsWithParent)
            )
        );
    }

    public function testGetNonExistentMenu(): void
    {
        $response = $this->cget(
            ['entity' => 'menus'],
            ['filter' => ['menu' => 'non_existent_menu']]
        );

        self::assertResponseStatusCodeEquals($response, Response::HTTP_OK);
        $content = self::jsonToArray($response->getContent());
        self::assertArrayHasKey('data', $content);
        self::assertIsArray($content['data']);
        self::assertEmpty($content['data'], 'Non-existent menu should return empty array');
    }

    public function testTryToGet(): void
    {
        $response = $this->get(
            ['entity' => 'menus', 'id' => 'test_menu'],
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
            [
                'entity' => 'menus',
                'id' => 'test_menu'
            ],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    public function testTryToDelete(): void
    {
        $response = $this->delete(
            [
                'entity' => 'menus',
                'id' => 'test_menu'
            ],
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
            [],
            [],
            false
        );
        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }
}
