<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\FrontendTestFrameworkBundle\Migrations\Data\ORM\LoadCustomerUserData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class AuditControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader(LoadCustomerUserData::AUTH_USER, LoadCustomerUserData::AUTH_PW)
        );
        $this->client->useHashNavigation(true);
    }

    /**
     * It is assumed that route "oro_customer_frontend_dataaudit_history" does not exist as per BAP-9497.
     */
    public function testAuditHistoryRouteDoesNotExist()
    {
        $this->expectException(RouteNotFoundException::class);

        $this->getUrl(
            'oro_customer_frontend_dataaudit_history',
            [
                'entity' => 'Oro_Bundle_CustomerBundle_Entity_CustomerUser',
                'id' => $this->getCurrentUser()->getId(),
            ]
        );
    }

    private function getCurrentUser(): CustomerUser
    {
        return $this->getContainer()->get('doctrine')->getRepository(CustomerUser::class)
            ->findOneBy(['username' => LoadCustomerUserData::AUTH_USER]);
    }
}
