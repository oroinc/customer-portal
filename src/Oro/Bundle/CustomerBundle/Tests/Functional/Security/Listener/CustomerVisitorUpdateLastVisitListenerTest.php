<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Security\Listener;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CustomerVisitorUpdateLastVisitListenerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
    }

    public function testMaintenanceMode()
    {
        $driver = self::getContainer()->get('oro_maintenance.driver.factory')->getDriver();
        $driver->lock();
        $this->client->request('GET', $this->getUrl('oro_frontend_root'));

        $this->assertHtmlResponseStatusCodeEquals(
            $this->client->getResponse(),
            Response::HTTP_SERVICE_UNAVAILABLE
        );

        $driver->unlock();
    }
}
