<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Controller;

use Oro\Bundle\FrontendTestFrameworkBundle\Migrations\Data\ORM\LoadCustomerUserData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolationPerTest
 */
class ErrorControllerTest extends WebTestCase
{
    public function testShowActionNotFoundFrontend(): void
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader(LoadCustomerUserData::AUTH_USER, LoadCustomerUserData::AUTH_PW)
        );

        $this->client->followRedirects();
        $this->client->request('GET', '/page-does-not-exist');
        $response = $this->getClientInstance()->getResponse();

        $this->assertResponseStatusCodeEquals($response, 404);
        $this->assertResponseContentTypeContains($response, 'text/html');
        self::assertStringContainsString('Not Found', $response->getContent());
        self::assertStringContainsString(
            'Shopping List',
            $response->getContent(),
            'Failed asserting that the error page is rendered by layouts.'
        );
    }

    public function testShowActionNotFoundBackend(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);

        $this->client->followRedirects();
        $this->client->request('GET', '/admin/page-does-not-exist');
        $response = $this->getClientInstance()->getResponse();

        $this->assertResponseStatusCodeEquals($response, 404);
        $this->assertResponseContentTypeContains($response, 'text/html');
        self::assertStringContainsString('Not Found', $response->getContent());
    }

    public function testWhenMaintenanceModeFrontend(): void
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader(LoadCustomerUserData::AUTH_USER, LoadCustomerUserData::AUTH_PW)
        );

        $maintenance = $this->getContainer()->get('oro_maintenance.maintenance');
        $maintenance->activate();
        try {
            $this->client->followRedirects();
            $this->client->request('GET', '/page-does-not-exist');
            $response = $this->getClientInstance()->getResponse();
        } finally {
            $maintenance->off();
        }

        $this->assertResponseStatusCodeEquals($response, 503);
        $this->assertResponseContentTypeContains($response, 'text/html');
        self::assertStringContainsString(
            'The System is currently under maintenance and should be available in a few minutes.',
            $response->getContent()
        );
    }

    public function testWhenMaintenanceModeBackend(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());

        $maintenance = $this->getContainer()->get('oro_maintenance.maintenance');
        $maintenance->activate();
        try {
            $this->client->followRedirects();
            $this->client->request('GET', '/admin');
            $response = $this->getClientInstance()->getResponse();
        } finally {
            $maintenance->off();
        }

        $this->assertResponseStatusCodeEquals($response, 503);
        $this->assertResponseContentTypeContains($response, 'text/html');
        self::assertStringContainsString(
            'The System is currently under maintenance and should be available in a few minutes.',
            $response->getContent()
        );
    }
}
