<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller\Frontend\Api\Rest;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\CustomerBundle\Entity\GridView;
use Oro\Bundle\CustomerBundle\Entity\GridViewUser;
use Oro\Bundle\CustomerBundle\Entity\Repository\GridViewUserRepository;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserGridViewACLData;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadGridViewData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class GridViewControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->initClient(
            [],
            $this->generateBasicAuthHeader(
                LoadCustomerUserGridViewACLData::USER_ACCOUNT_2_ROLE_LOCAL,
                LoadCustomerUserGridViewACLData::USER_ACCOUNT_2_ROLE_LOCAL
            )
        );
        $this->loadFixtures([LoadGridViewData::class]);
    }

    public function testPostActionWithIncorrectData()
    {
        $this->client->jsonRequest('POST', $this->getUrl('oro_api_frontend_datagrid_gridview_post'));
        $this->assertJsonResponseStatusCodeEquals($this->client->getResponse(), 400);
    }

    public function testPostAction()
    {
        $this->client->jsonRequest(
            'POST',
            $this->getUrl('oro_api_frontend_datagrid_gridview_post'),
            [
                'label' => 'test view 1',
                'type' => GridView::TYPE_PUBLIC,
                'grid_name' => 'items-grid',
                'filters' => [],
                'sorters' => []
            ]
        );

        $this->assertJsonResponseStatusCodeEquals($this->client->getResponse(), 201);

        $response = json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('id', $response);

        $createdGridView = $this->findGridView($response['id']);

        $this->assertNotNull($createdGridView);
        $this->assertEquals(
            LoadCustomerUserGridViewACLData::USER_ACCOUNT_2_ROLE_LOCAL,
            $createdGridView->getOwner()->getUsername()
        );
        $this->assertEquals('test view 1', $createdGridView->getName());
        $this->assertEquals(GridView::TYPE_PUBLIC, $createdGridView->getType());
        $this->assertEquals('items-grid', $createdGridView->getGridName());
        $this->assertEmpty($createdGridView->getFiltersData());
        $this->assertEmpty($createdGridView->getSortersData());
    }

    public function testPutActionPublicWithNoEditPermissions()
    {
        $this->loginUser(LoadCustomerUserGridViewACLData::USER_ACCOUNT_1_ROLE_LOCAL);

        /** @var GridView $gridView */
        $gridView = $this->getReference(LoadGridViewData::GRID_VIEW_PUBLIC);

        $this->client->jsonRequest(
            'PUT',
            $this->getUrl('oro_api_frontend_datagrid_gridview_put', ['id' => $gridView->getId()]),
            [
                'label' => 'forbidden by edit permission',
                'type' => GridView::TYPE_PUBLIC,
                'grid_name' => 'items-grid',
                'filters' => [],
                'sorters' => []
            ]
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), 403);
    }

    public function testPutActionPrivateWithNoCreatePermissions()
    {
        /** @var GridView $gridView */
        $gridView = $this->getReference(LoadGridViewData::GRID_VIEW_1);

        $this->client->jsonRequest(
            'PUT',
            $this->getUrl('oro_api_frontend_datagrid_gridview_put', ['id' => $gridView->getId()]),
            [
                'label' => 'forbidden by create permission',
                'type' => GridView::TYPE_PUBLIC,
                'grid_name' => 'items-grid',
                'filters' => [],
                'sorters' => [],
            ]
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), 403);
    }

    public function testPutAction()
    {
        /** @var GridView $gridViewPrivate */
        $gridViewPrivate = $this->getReference(LoadGridViewData::GRID_VIEW_PRIVATE);
        $id = $gridViewPrivate->getId();

        $this->client->jsonRequest(
            'PUT',
            $this->getUrl('oro_api_frontend_datagrid_gridview_put', ['id' => $id]),
            [
                'label' => 'test view 2',
                'type' => GridView::TYPE_PUBLIC,
                'grid_name' => 'items-grid',
                'filters' => [
                    'username' => ['type' => 1, 'value' => 'test']
                ],
                'sorters' => [
                    'username' => 1
                ],
            ]
        );
        $this->assertResponseStatusCodeEquals($this->client->getResponse(), 204);

        $updatedGridView = $this->findGridView($id);

        $this->assertEquals(
            LoadCustomerUserGridViewACLData::USER_ACCOUNT_2_ROLE_LOCAL,
            $updatedGridView->getOwner()->getUsername()
        );
        $this->assertEquals('test view 2', $updatedGridView->getName());
        $this->assertEquals(GridView::TYPE_PUBLIC, $updatedGridView->getType());
        $this->assertEquals('items-grid', $updatedGridView->getGridName());
        $this->assertEquals(
            [
                'username' => ['type' => 1, 'value' => 'test']
            ],
            $updatedGridView->getFiltersData()
        );
        $this->assertEquals(['username' => 1], $updatedGridView->getSortersData());
    }

    public function testDeleteAction()
    {
        /** @var GridView $gridViewPublic */
        $gridViewPublic = $this->getReference(LoadGridViewData::GRID_VIEW_PUBLIC);
        $id = $gridViewPublic->getId();

        $this->assertNotNull($this->findGridView($id));

        $this->client->jsonRequest(
            'DELETE',
            $this->getUrl('oro_api_frontend_datagrid_gridview_delete', ['id' => $id])
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), 204);
        $this->assertNull($this->findGridView($id));
    }

    public function testDefaultAction()
    {
        /** @var GridViewUserRepository $repository */
        $repository = $this->getRepository(GridViewUser::class);

        /** @var GridView $gridView */
        $gridView = $this->getReference(LoadGridViewData::GRID_VIEW_PRIVATE);
        $id = $gridView->getId();

        $this->assertEmpty($repository->findAll());

        $this->client->jsonRequest(
            'POST',
            $this->getUrl(
                'oro_api_frontend_datagrid_gridview_default',
                [
                    'id' => $id,
                    'default' => true,
                    'gridName' => 'items-grid'
                ]
            )
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), 204);
        $this->assertNotEmpty($repository->findAll());
    }

    private function findGridView(int $id): ?GridView
    {
        return $this->getRepository(GridView::class)->find($id);
    }

    private function getRepository(string $className): EntityRepository
    {
        return self::getContainer()->get('doctrine')->getRepository($className);
    }
}
