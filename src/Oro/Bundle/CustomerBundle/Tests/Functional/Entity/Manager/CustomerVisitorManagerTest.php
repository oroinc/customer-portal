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
    #[\Override]
    protected function setUp(): void
    {
        $this->initClient();
    }

    private function getDoctrine(): ManagerRegistry
    {
        return self::getContainer()->get('doctrine');
    }

    private function customerUserLoginRequest(): void
    {
        $this->client->request(
            'GET',
            $this->getUrl('oro_customer_customer_user_security_login'),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );
    }

    public function testCreateWithDefaultConnection(): void
    {
        $manager = new CustomerVisitorManager($this->getDoctrine());
        self::assertInstanceOf(CustomerVisitor::class, $manager->findOrCreate(null));
    }

    public function testCreateWithSessionConnection(): void
    {
        $manager = new CustomerVisitorManager($this->getDoctrine(), 'session');
        self::assertInstanceOf(CustomerVisitor::class, $manager->findOrCreate(null));
    }

    public function testAnonymousCustomerVisitorCookies(): void
    {
        $this->customerUserLoginRequest();

        $responseCookies = $this->client->getResponse()->headers->getCookies();
        $anonymousVisitorCookieExists = false;
        foreach ($responseCookies as $cookie) {
            if ($cookie->getName() === AnonymousCustomerUserAuthenticator::COOKIE_NAME) {
                $cookieValue = $cookie->getValue();
                $sessionId = json_decode(base64_decode($cookieValue), null, 2, JSON_THROW_ON_ERROR);
                self::assertIsString($sessionId);
                self::assertNotEmpty($sessionId);
                $anonymousVisitorCookieExists = true;
            }
        }
        self::assertTrue($anonymousVisitorCookieExists);
    }

    public function testCustomerVisitorInsertion(): void
    {
        $customerVisitorRepository = $this->getDoctrine()->getRepository(CustomerVisitor::class);
        $countCustomerVisitors = $customerVisitorRepository->count([]);

        $this->customerUserLoginRequest();

        self::assertEquals($countCustomerVisitors, $customerVisitorRepository->count([]));
    }
}
