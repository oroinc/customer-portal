<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ControllerFrontend;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\FrontendTestFrameworkBundle\Migrations\Data\ORM\LoadCustomerUserData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolationPerTest
 */
final class CustomerUserEmailChangeControllerTest extends WebTestCase
{
    private const string CSRF_TOKEN_ID = 'oro_customer_frontend_customer_user_cancel_user_view';
    private const string NEW_EMAIL = 'new.email@example.com';

    #[\Override]
    protected function setUp(): void
    {
        $this->initClient(
            [],
            self::generateBasicAuthHeader(LoadCustomerUserData::AUTH_USER, LoadCustomerUserData::AUTH_PW)
        );
    }

    public function testCancelWithCsrfToken(): void
    {
        $customerUser = $this->createCustomerUserWithPendingEmailChange();

        $this->client->request(
            'POST',
            $this->getUrl(self::CSRF_TOKEN_ID, ['id' => $customerUser->getId()]),
            ['_token' => $this->getCsrfToken(self::CSRF_TOKEN_ID)->getValue()]
        );

        self::assertResponseStatusCodeEquals($this->client->getResponse(), 302);
        $this->getEntityManager()->refresh($customerUser);
        self::assertNull($customerUser->getNewEmail());
    }

    public function testCancelWithoutCsrfToken(): void
    {
        $customerUser = $this->createCustomerUserWithPendingEmailChange();

        $this->client->request('POST', $this->getUrl(self::CSRF_TOKEN_ID, ['id' => $customerUser->getId()]));

        self::assertResponseStatusCodeEquals($this->client->getResponse(), 403);

        $this->getEntityManager()->refresh($customerUser);
        self::assertSame(self::NEW_EMAIL, $customerUser->getNewEmail());
    }

    public function testCancelWithInvalidCsrfToken(): void
    {
        $customerUser = $this->createCustomerUserWithPendingEmailChange();
        $this->getCsrfToken(self::CSRF_TOKEN_ID);

        $this->client->request(
            'POST',
            $this->getUrl(self::CSRF_TOKEN_ID, ['id' => $customerUser->getId()]),
            ['_token' => 'invalid']
        );

        self::assertResponseStatusCodeEquals($this->client->getResponse(), 403);

        $this->getEntityManager()->refresh($customerUser);
        self::assertSame(self::NEW_EMAIL, $customerUser->getNewEmail());
    }

    public function testCancelWithGetRequest(): void
    {
        $customerUser = $this->createCustomerUserWithPendingEmailChange();

        $this->client->request('GET', $this->getUrl(self::CSRF_TOKEN_ID, ['id' => $customerUser->getId()]));

        self::assertResponseStatusCodeEquals($this->client->getResponse(), 405);
        $this->getEntityManager()->refresh($customerUser);
        self::assertSame(self::NEW_EMAIL, $customerUser->getNewEmail());
    }

    private function createCustomerUserWithPendingEmailChange(): CustomerUser
    {
        $entityManager = $this->getEntityManager();
        $customerUser = $entityManager->getRepository(CustomerUser::class)
            ->findOneBy(['email' => LoadCustomerUserData::AUTH_USER]);
        $customerUser->setNewEmail(self::NEW_EMAIL);
        $entityManager->flush();

        return $customerUser;
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return $this->getContainer()->get('doctrine')->getManagerForClass(CustomerUser::class);
    }
}
