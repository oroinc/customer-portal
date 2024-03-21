<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ControllerFrontendRestApi;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CountryRegionsControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
    }

    public function testGetAction()
    {
        $this->client->jsonRequest('GET', $this->getUrl('oro_api_frontend_country_get_regions', ['country' => 'US']));

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertNotEmpty($result);
    }
}
