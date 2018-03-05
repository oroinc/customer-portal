<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Controller\Api;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class ApiDocControllerTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient();
    }

    public function testUnknownView()
    {
        $this->client->request(
            'GET',
            $this->getUrl('oro_frontend_rest_api_doc', ['view' => 'unknown'])
        );
        self::assertResponseStatusCodeEquals($this->client->getResponse(), 404);
    }

    public function testDefaultView()
    {
        try {
            $this->client->request(
                'GET',
                $this->getUrl('oro_frontend_rest_api_doc')
            );
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
        $this->client->request(
            'GET',
            $this->getUrl('oro_frontend_rest_api_doc', ['view' => 'rest_json_api'])
        );
        self::assertResponseStatusCodeEquals($this->client->getResponse(), 404);
    }

    public function testFrontendViewViaBackendController()
    {
        $this->client->request(
            'GET',
            $this->getUrl('nelmio_api_doc_index', ['view' => 'frontend_rest_json_api'])
        );
        self::assertResponseStatusCodeEquals($this->client->getResponse(), 404);
    }
}
