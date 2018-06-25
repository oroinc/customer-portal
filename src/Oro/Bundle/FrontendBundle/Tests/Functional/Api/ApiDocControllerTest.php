<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Api;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group regression
 */
class ApiDocControllerTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient();
    }

    /**
     * @param string|null $view
     * @param string      $route
     *
     * @return Response
     */
    private function sendApiDocRequest(string $view = null, string $route = 'oro_frontend_rest_api_doc'): Response
    {
        $parameters = [];
        if (null !== $view) {
            $parameters['view'] = $view;
        }
        $this->client->request(
            'GET',
            $this->getUrl($route, $parameters)
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
        try {
            $this->sendApiDocRequest();
        } catch (\PHPUnit_Framework_AssertionFailedError $e) {
            // ignore checkForBackendUrls,
            // because urls on API sandbox are updated by JS
            // due to hardcode in NelmioApiDocBuntle TWIG template
            // see NelmioApiDocBundle/Resources/views/layout.html.twig
            if (false === strpos($e->getMessage(), 'contains backend prefix')) {
                throw $e;
            }
        }
        self::assertResponseStatusCodeEquals($this->client->getResponse(), 200);
    }

    public function testBackendViewViaFrontendController()
    {
        $response = $this->sendApiDocRequest('rest_json_api');
        self::assertResponseStatusCodeEquals($response, 404);
    }

    public function testFrontendViewViaBackendController()
    {
        $response = $this->sendApiDocRequest('frontend_rest_json_api', 'nelmio_api_doc_index');
        self::assertResponseStatusCodeEquals($response, 404);
    }
}
