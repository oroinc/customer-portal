<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * @dbIsolationPerTest
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class HateoasTest extends FrontendRestJsonApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->enableVisitor();
        $this->loadVisitor();
    }

    private function loadProductEntities()
    {
        $this->loadFixtures([
            '@OroFrontendBundle/Tests/Functional/Api/DataFixtures/products.yml'
        ]);
    }

    private function loadProductEntitiesForPagination()
    {
        $this->loadFixtures([
            '@OroFrontendBundle/Tests/Functional/Api/DataFixtures/products_for_pagination.yml'
        ]);
    }

    private function getExpectedContent(array|string $expectedContent, array|string|null $entityId = null): array
    {
        if (is_string($expectedContent)) {
            $expectedContent = $this->loadData($expectedContent, $this->getResponseDataFolderName());
        } else {
            $expectedContent = Yaml::dump($expectedContent);
        }
        if (null === $entityId) {
            $valueMap = [];
        } else {
            $valueMap = is_array($entityId)
                ? $entityId
                : ['productId' => $entityId];
        }
        $valueMap['baseUrl'] = $this->getApiBaseUrl();
        foreach ($valueMap as $key => $value) {
            $expectedContent = str_replace('{' . $key . '}', $value, $expectedContent);
        }

        return self::processTemplateData(Yaml::parse($expectedContent));
    }

    public function testGetList()
    {
        $this->loadProductEntities();

        $product1Id = $this->getReference('product1')->getId();
        $product2Id = $this->getReference('product2')->getId();
        $response = $this->cget(
            ['entity' => 'testproducts'],
            [],
            ['HTTP_HATEOAS' => true]
        );

        $this->assertResponseContains(
            $this->getExpectedContent(
                'hateoas_cget.yml',
                [
                    'product1Id' => (string)$product1Id,
                    'product2Id' => (string)$product2Id
                ]
            ),
            $response
        );
    }

    public function testGet()
    {
        $this->loadProductEntities();

        $productId = $this->getReference('product1')->getId();
        $response = $this->get(
            ['entity' => 'testproducts', 'id' => (string)$productId],
            [],
            ['HTTP_HATEOAS' => true]
        );

        $this->assertResponseContains(
            $this->getExpectedContent('hateoas_get.yml', (string)$productId),
            $response
        );
    }

    public function testGetWithIncludedEntities()
    {
        $this->loadProductEntities();

        $productId = $this->getReference('product1')->getId();
        $productTypeId = $this->getReference('product_type1')->getName();
        $response = $this->get(
            ['entity' => 'testproducts', 'id' => (string)$productId],
            ['include' => 'productType'],
            ['HTTP_HATEOAS' => true]
        );

        $this->assertResponseContains(
            $this->getExpectedContent(
                'hateoas_get_included.yml',
                [
                    'productId'     => (string)$productId,
                    'productTypeId' => (string)$productTypeId
                ]
            ),
            $response
        );
    }

    public function testCreate()
    {
        $data = [
            'data'     => [
                'type'          => 'testproducts',
                'attributes'    => [
                    'name' => 'New Product'
                ],
                'relationships' => [
                    'productType' => [
                        'data' => [
                            'type' => 'testproducttypes',
                            'id'   => 'product_type1'
                        ]
                    ]
                ]
            ],
            'included' => [
                [
                    'type'       => 'testproducttypes',
                    'id'         => 'product_type1',
                    'attributes' => [
                        'label' => 'New Product Type'
                    ]
                ]
            ]
        ];
        $response = $this->post(
            ['entity' => 'testproducts'],
            $data,
            ['HTTP_HATEOAS' => true]
        );

        $productId = $this->getResourceId($response);
        $productType1Id = self::getNewResourceIdFromIncludedSection($response, 'product_type1');

        $this->assertResponseContains(
            $this->getExpectedContent(
                'hateoas_create.yml',
                [
                    'productId'      => $productId,
                    'productType1Id' => $productType1Id
                ]
            ),
            $response
        );
    }

    public function testUpdate()
    {
        $this->loadProductEntities();

        $productId = $this->getReference('product1')->getId();
        $response = $this->patch(
            ['entity' => 'testproducts', 'id' => (string)$productId],
            [
                'data' => [
                    'type'       => 'testproducts',
                    'id'         => (string)$productId,
                    'attributes' => [
                        'name' => 'Updated Name'
                    ]
                ]
            ],
            ['HTTP_HATEOAS' => true]
        );

        $this->assertResponseContains(
            $this->getExpectedContent('hateoas_update.yml', (string)$productId),
            $response
        );
    }

    public function testGetSubresource()
    {
        $this->loadProductEntities();

        $productId = $this->getReference('product1')->getId();
        $productTypeId = $this->getReference('product_type1')->getName();
        $response = $this->getSubresource(
            ['entity' => 'testproducts', 'id' => (string)$productId, 'association' => 'productType'],
            [],
            ['HTTP_HATEOAS' => true]
        );

        $this->assertResponseContains(
            $this->getExpectedContent(
                'hateoas_get_subresource.yml',
                [
                    'productId'     => (string)$productId,
                    'productTypeId' => (string)$productTypeId
                ]
            ),
            $response
        );
    }

    public function testGetRelationship()
    {
        $this->loadProductEntities();

        $productId = $this->getReference('product1')->getId();
        $productTypeId = $this->getReference('product_type1')->getName();
        $response = $this->getRelationship(
            ['entity' => 'testproducts', 'id' => (string)$productId, 'association' => 'productType'],
            [],
            ['HTTP_HATEOAS' => true]
        );

        $this->assertResponseContains(
            $this->getExpectedContent(
                'hateoas_get_relationship.yml',
                [
                    'productId'     => (string)$productId,
                    'productTypeId' => (string)$productTypeId
                ]
            ),
            $response
        );
    }

    public function testGetListWithPaginationLinksFirstPage()
    {
        $this->loadProductEntitiesForPagination();

        $response = $this->cget(
            ['entity' => 'testproducts'],
            [],
            ['HTTP_HATEOAS' => true]
        );

        $expectedLinks = $this->getExpectedContent([
            'links' => [
                'self' => '{baseUrl}/testproducts',
                'next' => '{baseUrl}/testproducts?page%5Bnumber%5D=2'
            ]
        ]);
        $this->assertResponseContains($expectedLinks, $response);
    }

    public function testGetListWithPaginationLinksSecondPage()
    {
        $this->loadProductEntitiesForPagination();

        $response = $this->cget(
            ['entity' => 'testproducts'],
            ['page[number]' => 2],
            ['HTTP_HATEOAS' => true]
        );

        $expectedLinks = $this->getExpectedContent([
            'links' => [
                'self'  => '{baseUrl}/testproducts',
                'first' => '{baseUrl}/testproducts',
                'prev'  => '{baseUrl}/testproducts',
                'next'  => '{baseUrl}/testproducts?page%5Bnumber%5D=3'
            ]
        ]);
        $this->assertResponseContains($expectedLinks, $response);
    }

    public function testGetListWithPaginationLinksThirdPage()
    {
        $this->loadProductEntitiesForPagination();

        $response = $this->cget(
            ['entity' => 'testproducts'],
            ['page[number]' => 3],
            ['HTTP_HATEOAS' => true]
        );

        $expectedLinks = $this->getExpectedContent([
            'links' => [
                'self'  => '{baseUrl}/testproducts',
                'first' => '{baseUrl}/testproducts',
                'prev'  => '{baseUrl}/testproducts?page%5Bnumber%5D=2',
                'next'  => '{baseUrl}/testproducts?page%5Bnumber%5D=4'
            ]
        ]);
        $this->assertResponseContains($expectedLinks, $response);
    }

    public function testGetListWithPaginationLinksLastPage()
    {
        $this->loadProductEntitiesForPagination();

        $response = $this->cget(
            ['entity' => 'testproducts'],
            ['page[number]' => 4],
            ['HTTP_HATEOAS' => true]
        );

        $expectedLinks = $this->getExpectedContent([
            'links' => [
                'self'  => '{baseUrl}/testproducts',
                'first' => '{baseUrl}/testproducts',
                'prev'  => '{baseUrl}/testproducts?page%5Bnumber%5D=3'
            ]
        ]);
        $this->assertResponseContains($expectedLinks, $response);
    }

    public function testGetListWithPaginationLinksWhenThereAreOtherFilters()
    {
        $this->loadProductEntitiesForPagination();

        $response = $this->cget(
            ['entity' => 'testproducts'],
            ['page[number]' => 3, 'sort' => '-id', 'fields[testproducts]' => 'name'],
            ['HTTP_HATEOAS' => true]
        );

        $expectedLinks = $this->getExpectedContent([
            'links' => [
                'self'  => '{baseUrl}/testproducts',
                'first' => '{baseUrl}/testproducts?fields%5Btestproducts%5D=name&sort=-id',
                'prev'  => '{baseUrl}/testproducts?fields%5Btestproducts%5D=name&page%5Bnumber%5D=2&sort=-id',
                'next'  => '{baseUrl}/testproducts?fields%5Btestproducts%5D=name&page%5Bnumber%5D=4&sort=-id'
            ]
        ]);
        $this->assertResponseContains($expectedLinks, $response);
    }
}
