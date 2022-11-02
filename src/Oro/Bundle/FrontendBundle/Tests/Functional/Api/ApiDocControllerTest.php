<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Api;

use Oro\Bundle\ApiBundle\ApiDoc\RestDocUrlGenerator as BackendRestDocUrlGenerator;
use Oro\Bundle\ApiBundle\Tests\Functional\ApiFeatureTrait;
use Oro\Bundle\FrontendBundle\Api\ApiDoc\RestDocUrlGenerator as FrontendRestDocUrlGenerator;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group regression
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ApiDocControllerTest extends WebTestCase
{
    use ApiFeatureTrait;

    private const API_FEATURE_NAME = 'oro_frontend.web_api';

    protected function setUp(): void
    {
        $this->initClient();
    }

    private function sendApiDocRequest(string $view = null, string $route = null): Response
    {
        $parameters = [];
        if (null !== $view) {
            $parameters['view'] = $view;
        }
        if (!$route) {
            $route = FrontendRestDocUrlGenerator::ROUTE;
        }
        $this->client->request(
            'GET',
            $this->getUrl($route, $parameters)
        );

        return $this->client->getResponse();
    }

    private function sendApiDocResourceRequest(string $view, string $method, string $resource): Response
    {
        $resourceId = '/api/' . $resource;
        $resourceId = str_replace('/', '-', $resourceId);
        $resourceId = $method . '-' . $resourceId;

        $this->client->request(
            'GET',
            $this->getUrl(
                FrontendRestDocUrlGenerator::RESOURCE_ROUTE,
                ['view' => $view, 'resource' => $resourceId]
            )
        );

        return $this->client->getResponse();
    }

    public function testUnknownView()
    {
        $response = $this->sendApiDocRequest('unknown');
        self::assertResponseStatusCodeEquals($response, 404);
    }

    public function testDefaultView()
    {
        $this->sendApiDocRequest();
        self::assertResponseStatusCodeEquals($this->client->getResponse(), 200);
    }

    public function testBackendViewViaFrontendController()
    {
        $response = $this->sendApiDocRequest('rest_json_api');
        self::assertResponseStatusCodeEquals($response, 404);
    }

    public function testFrontendViewViaBackendController()
    {
        $response = $this->sendApiDocRequest('frontend_rest_json_api', BackendRestDocUrlGenerator::ROUTE);
        self::assertResponseStatusCodeEquals($response, 404);
    }

    public function testResource()
    {
        $response = $this->sendApiDocResourceRequest('frontend_rest_json_api', 'get', 'countries');
        self::assertResponseStatusCodeEquals($response, Response::HTTP_OK);
    }

    public function testResourceForUnknownView()
    {
        $response = $this->sendApiDocResourceRequest('unknown', 'get', 'countries');
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    public function testUnknownResource()
    {
        $response = $this->sendApiDocResourceRequest('frontend_rest_json_api', 'get', 'unknown');
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    public function testViewOnDisabledFeature()
    {
        $this->disableApiFeature(self::API_FEATURE_NAME);
        try {
            $response = $this->sendApiDocRequest('frontend_rest_json_api');
        } finally {
            $this->enableApiFeature(self::API_FEATURE_NAME);
        }
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    public function testResourceOnDisabledFeature()
    {
        $this->disableApiFeature(self::API_FEATURE_NAME);
        try {
            $response = $this->sendApiDocResourceRequest('frontend_rest_json_api', 'get', 'countries');
        } finally {
            $this->enableApiFeature(self::API_FEATURE_NAME);
        }
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    /**
     * @dataProvider notAllowedMethods
     */
    public function testNotAllowedMethod(string $method)
    {
        $this->client->request($method, $this->getUrl(FrontendRestDocUrlGenerator::ROUTE));
        $response = $this->client->getResponse();
        self::assertResponseStatusCodeEquals($response, Response::HTTP_METHOD_NOT_ALLOWED);
        self::assertAllowResponseHeader($response, 'GET');
    }

    /**
     * @dataProvider notAllowedMethods
     */
    public function testResourceNotAllowedMethod(string $method)
    {
        $this->client->request(
            $method,
            $this->getUrl(
                FrontendRestDocUrlGenerator::RESOURCE_ROUTE,
                ['view' => 'frontend_rest_json_api', 'resource' => 'test']
            )
        );
        $response = $this->client->getResponse();
        self::assertResponseStatusCodeEquals($response, Response::HTTP_METHOD_NOT_ALLOWED);
        self::assertAllowResponseHeader($response, 'GET');
    }

    /**
     * @dataProvider notAllowedMethods
     */
    public function testNotAllowedMethodForUnknownView(string $method)
    {
        $this->client->request($method, $this->getUrl(FrontendRestDocUrlGenerator::ROUTE, ['view' => 'unknown']));
        $response = $this->client->getResponse();
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    /**
     * @dataProvider notAllowedMethods
     */
    public function testResourceNotAllowedMethodForUnknownView(string $method)
    {
        $this->client->request(
            $method,
            $this->getUrl(
                FrontendRestDocUrlGenerator::RESOURCE_ROUTE,
                ['view' => 'unknown', 'resource' => 'test']
            )
        );
        $response = $this->client->getResponse();
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    /**
     * @dataProvider notAllowedMethods
     */
    public function testNotAllowedMethodOnDisabledFeature(string $method)
    {
        $this->disableApiFeature(self::API_FEATURE_NAME);
        try {
            $this->client->request($method, $this->getUrl(FrontendRestDocUrlGenerator::ROUTE));
            $response = $this->client->getResponse();
        } finally {
            $this->enableApiFeature(self::API_FEATURE_NAME);
        }
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    /**
     * @dataProvider notAllowedMethods
     */
    public function testResourceNotAllowedMethodOnDisabledFeature(string $method)
    {
        $this->disableApiFeature(self::API_FEATURE_NAME);
        try {
            $this->client->request(
                $method,
                $this->getUrl(
                    FrontendRestDocUrlGenerator::RESOURCE_ROUTE,
                    ['view' => 'frontend_rest_json_api', 'resource' => 'test']
                )
            );
            $response = $this->client->getResponse();
        } finally {
            $this->enableApiFeature(self::API_FEATURE_NAME);
        }
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    public function notAllowedMethods(): array
    {
        return [
            ['POST'],
            ['PATCH'],
            ['PUT'],
            ['DELETE'],
            ['OPTIONS'],
            ['HEAD'],
        ];
    }
}
