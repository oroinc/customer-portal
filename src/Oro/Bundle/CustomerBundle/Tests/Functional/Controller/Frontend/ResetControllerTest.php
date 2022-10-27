<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller\Frontend;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class ResetControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
        $this->client->useHashNavigation(true);
    }

    public function testResetWithEmptyToken()
    {
        $this->client->request('GET', $this->getUrl(
            'oro_customer_frontend_customer_user_password_reset'
        ));

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), 404);
    }

    public function testResetWithUnknownToken()
    {
        $this->client->request('GET', $this->getUrl(
            'oro_customer_frontend_customer_user_password_reset',
            ['token' => 'unknown']
        ));

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), 404);
    }
}
