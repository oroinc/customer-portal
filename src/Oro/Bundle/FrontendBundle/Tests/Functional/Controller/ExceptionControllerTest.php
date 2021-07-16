<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Controller;

use Oro\Bundle\FrontendTestFrameworkBundle\Migrations\Data\ORM\LoadCustomerUserData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\Testing\ResponseExtension;

class ExceptionControllerTest extends WebTestCase
{
    use ResponseExtension;

    public function testShowActionNotFoundFrontend(): void
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader(LoadCustomerUserData::AUTH_USER, LoadCustomerUserData::AUTH_PW)
        );

        $this->client->followRedirects();
        $this->client->request('GET', '/page-does-not-exist');

        $this->assertLastResponseStatus(404);
        $this->assertLastResponseContentTypeHtml();
        static::assertStringContainsString('Not Found', $this->getClientInstance()->getResponse()->getContent());
    }

    public function testWhenMaintenanceModeFrontend(): void
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader(LoadCustomerUserData::AUTH_USER, LoadCustomerUserData::AUTH_PW)
        );

        $maintenance = $this->getContainer()->get('oro_maintenance.maintenance');
        $maintenance->activate();

        $this->client->followRedirects();
        $this->client->request('GET', '/page-does-not-exist');

        $this->assertLastResponseStatus(503);
        $this->assertLastResponseContentTypeHtml();
        static::assertStringContainsString(
            'The System is currently under maintenance and should be available in a few minutes.',
            $this->getClientInstance()->getResponse()->getContent()
        );

        $maintenance->off();
    }

    public function testWhenMaintenanceModeBackend(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());

        $maintenance = $this->getContainer()->get('oro_maintenance.maintenance');
        $maintenance->activate();

        $this->client->followRedirects();
        $this->client->request('GET', '/admin');

        $this->assertLastResponseStatus(503);
        $this->assertLastResponseContentTypeHtml();
        static::assertStringContainsString(
            'The System is currently under maintenance and should be available in a few minutes.',
            $this->getClientInstance()->getResponse()->getContent()
        );

        $maintenance->off();
    }

    public function testShowActionNotFoundBackend(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);

        $this->client->followRedirects();
        $this->client->request('GET', '/admin/page-does-not-exist');

        $this->assertLastResponseStatus(404);
        $this->assertLastResponseContentTypeHtml();
        static::assertStringContainsString('Not Found', $this->getClientInstance()->getResponse()->getContent());
    }
}
