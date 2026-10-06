<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Entity\Manager;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitorManager;
use Oro\Bundle\CustomerBundle\Security\AnonymousCustomerUserAuthenticator;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

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

        $sessionId = $this->getCustomerVisitorSessionIdFromResponse();
        self::assertIsString($sessionId);
        self::assertNotEmpty($sessionId);
    }

    public function testUnknownCustomerVisitorCookieIsReplaced(): void
    {
        $sessionId = 'client-chosen-session-id';
        $this->client->getCookieJar()->set(new Cookie(
            AnonymousCustomerUserAuthenticator::COOKIE_NAME,
            base64_encode(json_encode($sessionId, JSON_THROW_ON_ERROR))
        ));

        $this->customerUserLoginRequest();

        $newSessionId = $this->getCustomerVisitorSessionIdFromResponse();
        self::assertIsString($newSessionId);
        self::assertNotEmpty($newSessionId);
        self::assertNotSame($sessionId, $newSessionId);
    }

    public function testCustomerVisitorInsertion(): void
    {
        $customerVisitorRepository = $this->getDoctrine()->getRepository(CustomerVisitor::class);
        $countCustomerVisitors = $customerVisitorRepository->count([]);

        $this->customerUserLoginRequest();

        self::assertEquals($countCustomerVisitors, $customerVisitorRepository->count([]));
    }

    private function getCustomerVisitorSessionIdFromResponse(): ?string
    {
        foreach ($this->client->getResponse()->headers->getCookies() as $cookie) {
            if ($cookie->getName() === AnonymousCustomerUserAuthenticator::COOKIE_NAME) {
                return json_decode(base64_decode($cookie->getValue()), null, 2, JSON_THROW_ON_ERROR);
            }
        }

        return null;
    }
}
