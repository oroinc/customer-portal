<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class WrongApiUriRequestsTest extends FrontendRestJsonApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->enableVisitor();
        $this->loadVisitor();
    }

    public function testTryToGetAnotherApiResourceWithFullReplaceOfBaseUrl(): void
    {
        $baseUrl = $this->getUrl($this->getListRouteName(), ['entity' => 'testapientity1']);
        $additionalUrl = $this->getUrl($this->getItemRouteName(), ['entity' => 'products', 'id' => 1]);
        $slashesCount = substr_count($baseUrl, '/') - 1;

        $response = $this->request(
            'GET',
            $baseUrl . str_repeat('/..', $slashesCount) . $additionalUrl
        );

        $this->assertResponseContainsValidationError(
            [
                'status' => '404',
                'title'  => 'not found http exception',
                'detail' => 'No route found for "GET http://localhost/api/testapientity1/../api/products/1".'
            ],
            $response,
            Response::HTTP_NOT_FOUND
        );
    }

    public function testTryToGetAnotherApiResource(): void
    {
        $baseUrl = $this->getUrl($this->getListRouteName(), ['entity' => 'testapientity1']);

        $response = $this->request(
            'GET',
            $baseUrl . '/../products/1'
        );

        $this->assertResponseContainsValidationError(
            [
                'status' => '404',
                'title'  => 'not found http exception',
                'detail' => 'No route found for "GET http://localhost/api/testapientity1/../products/1"'
                    . ' (from "http://localhost/api/testapientity1/../api/products/1").'
            ],
            $response,
            Response::HTTP_NOT_FOUND
        );
    }

    public function testTryToGetProductViewPageThroughtApiRequest(): void
    {
        $baseUrl = $this->getUrl($this->getListRouteName(), ['entity' => 'testapientity1']);
        $additionalUrl = self::getContainer()->get('router')
            ->generate('oro_product_frontend_product_view', ['id' => 1], UrlGeneratorInterface::ABSOLUTE_PATH);
        $slashesCount = substr_count($baseUrl, '/') - 1;

        $response = $this->request(
            'GET',
            $baseUrl . str_repeat('/..', $slashesCount) . $additionalUrl
        );

        $this->assertResponseContainsValidationError(
            [
                'status' => '404',
                'title'  => 'not found http exception',
                'detail' => 'No route found for "GET http://localhost/api/testapientity1/../product/view/1"'
                    . ' (from "http://localhost/api/testapientity1/../products/1").'
            ],
            $response,
            Response::HTTP_NOT_FOUND
        );
    }
}
