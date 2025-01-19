<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Entity\Manager;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitorManager;
use Oro\Bundle\CustomerBundle\Security\AnonymousCustomerUserAuthenticator;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolationPerTest
 */
class CustomerVisitorManagerTest extends WebTestCase
{
    public function setUp(): void
    {
        $this->initClient();
    }

    private function getDoctrine(): ManagerRegistry
    {
        return self::getContainer()->get('doctrine');
    }

    public function testCreateWithDefaultConnection()
    {
        $manager = new CustomerVisitorManager($this->getDoctrine());
        $this->assertInstanceOf(CustomerVisitor::class, $manager->findOrCreate());
    }

    public function testCreateWithSessionConnection()
    {
        $manager = new CustomerVisitorManager($this->getDoctrine(), 'session');
        $this->assertInstanceOf(CustomerVisitor::class, $manager->findOrCreate());
    }

    public function testAnonymousCustomerVisitorCookies(): void
    {
        $this->customerUserLoginRequest();

        $responseCookies = $this->client->getResponse()->headers->getCookies();
        $anonymousVisitorCookieExists = false;
        foreach ($responseCookies as $cookie) {
            if ($cookie->getName() === AnonymousCustomerUserAuthenticator::COOKIE_NAME) {
                $cookieValue = $cookie->getValue();
                [$visitorId, $sessionId] = json_decode(base64_decode($cookieValue));

                $this->assertNull($visitorId);
                $this->assertTrue(CustomerVisitor::isAnonymousSession($sessionId));
                $anonymousVisitorCookieExists = true;
            }
        }
        $this->assertTrue($anonymousVisitorCookieExists);
    }

    public function testCustomerVisitorInsertion(): void
    {
        $customerVisitorRepository = $this->getDoctrine()->getRepository(CustomerVisitor::class);
        $countCustomerVisitors = $customerVisitorRepository->count([]);

        $this->customerUserLoginRequest();

        $countCustomerVisitorsAfterRequest = $customerVisitorRepository->count([]);

        self::assertEquals($countCustomerVisitors, $countCustomerVisitorsAfterRequest);
    }

    protected function customerUserLoginRequest(): void
    {
        $this->client->request(
            'GET',
            $this->getUrl('oro_customer_customer_user_security_login'),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );
    }
}
