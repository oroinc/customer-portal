<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional;

use Oro\Bundle\ActionBundle\Tests\Functional\OperationAwareTestTrait;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Tests\Functional\Controller\EmailMessageAssertionTrait;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserACLData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Email as SymfonyEmail;

/**
 * @dbIsolationPerTest
 */
class CustomerUserFrontendOperationsTest extends WebTestCase
{
    use EmailMessageAssertionTrait;
    use OperationAwareTestTrait;

    protected function setUp(): void
    {
        $this->initClient();
        $this->client->useHashNavigation(true);
        $this->loadFixtures([LoadCustomerUserACLData::class]);
    }

    /**
     * @dataProvider accessGrantedDataProvider
     */
    public function testSendConfirmation(string $login, string $resource): void
    {
        $em = self::getContainer()->get('doctrine')->getManagerForClass(CustomerUser::class);
        $this->loginUser($login);

        $user = $this->findCustomerUser($resource);

        $user->setConfirmed(false);
        $em->flush();

        $this->executeOperation($user, 'oro_customer_customeruser_sendconfirmation');

        $result = $this->client->getResponse();
        self::assertJsonResponseStatusCodeEquals($result, Response::HTTP_OK);

        $emailMessages = self::getMailerMessages();
        self::assertCount(1, $emailMessages);

        /** @var SymfonyEmail $emailMessage */
        $emailMessage = array_shift($emailMessages);

        self::assertInstanceOf(SymfonyEmail::class, $emailMessage);
        self::assertEmailAddressContains($emailMessage, 'to', $resource);
        self::assertStringContainsString('Confirmation of account registration', $emailMessage->getSubject());
        self::assertStringContainsString($resource, $emailMessage->getHtmlBody());

        $user = $this->findCustomerUser($resource);
        $user->setConfirmed(true);
        $em->flush();
    }

    /**
     * @dataProvider accessDeniedDataProvider
     */
    public function testSendConfirmationAccessDenied(string $login, string $resource, int $status): void
    {
        $em = self::getContainer()->get('doctrine')->getManagerForClass(CustomerUser::class);
        $this->loginUser($login);

        $user = $this->findCustomerUser($resource);
        $user->setConfirmed(false);
        $em->flush();

        $this->client->getContainer()->get('doctrine')->getManager()->clear();

        $this->executeOperation($user, 'oro_customer_customeruser_sendconfirmation');
        self::assertSame($status, $this->client->getResponse()->getStatusCode());

        $user = $this->findCustomerUser($resource);
        $user->setConfirmed(true);
        $em->flush();
    }

    /**
     * @dataProvider accessGrantedDataProvider
     */
    public function testConfirmAccessGranted(string $login, string $resource): void
    {
        $em = self::getContainer()->get('doctrine')->getManagerForClass(CustomerUser::class);
        $this->loginUser($login);

        $user = $this->findCustomerUser($resource);
        $user->setConfirmed(false);
        $em->flush();

        $this->executeOperation($user, 'oro_customer_customeruser_confirm');
        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $user = $this->findCustomerUser($resource);
        self::assertTrue($user->isConfirmed());

        $emailMessages = self::getMailerMessages();
        self::assertCount(1, $emailMessages);

        /** @var SymfonyEmail $emailMessage */
        $emailMessage = array_shift($emailMessages);

        $this->assertWelcomeMessage($user->getEmail(), $emailMessage);
        self::assertStringContainsString(
            'Please follow the link below to create a password for your new account.',
            $emailMessage->getHtmlBody()
        );

        $user = $this->findCustomerUser($resource);
        self::assertTrue($user->isConfirmed());
    }

    /**
     * @dataProvider accessDeniedDataProvider
     */
    public function testConfirmAccessDenied(string $login, string $resource, int $status): void
    {
        $em = self::getContainer()->get('doctrine')->getManagerForClass(CustomerUser::class);
        $this->loginUser($login);

        $user = $this->findCustomerUser($resource);

        $user->setConfirmed(false);
        $em->flush();

        $this->executeOperation($user, 'oro_customer_customeruser_confirm');
        self::assertSame($status, $this->client->getResponse()->getStatusCode());

        $user = $this->findCustomerUser($resource);
        $user->setConfirmed(true);
        $em->flush();
    }

    /**
     * @dataProvider accessGrantedDataProvider
     */
    public function testEnableAndDisable(string $login, string $resource): void
    {
        $em = self::getContainer()->get('doctrine')->getManagerForClass(CustomerUser::class);
        $this->loginUser($login);

        $user = $this->findCustomerUser($resource);
        self::assertTrue($user->isEnabled());

        $this->executeOperation($user, 'oro_customer_frontend_customeruser_disable');
        self::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $user = $this->findCustomerUser($resource);
        self::assertFalse($user->isEnabled());
        self::assertNotEmpty($user->getUserRoles());
        $this->executeOperation($user, 'oro_customer_frontend_customeruser_enable');
        self::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $user = $this->findCustomerUser($resource);
        self::assertTrue($user->isEnabled());

        $user = $this->findCustomerUser($resource);
        $user->setConfirmed(true);
        $em->flush();
    }

    /**
     * @dataProvider accessDeniedDataProvider
     */
    public function testEnableAndDisableAccessDenied(string $login, string $resource, int $status): void
    {
        $em = self::getContainer()->get('doctrine')->getManagerForClass(CustomerUser::class);
        $this->loginUser($login);

        $user = $this->findCustomerUser($resource);
        $user->setConfirmed(false);
        $em->flush();
        $this->executeOperation($user, 'oro_customer_frontend_customeruser_enable');
        self::assertSame($status, $this->client->getResponse()->getStatusCode());

        $user = $this->findCustomerUser($resource);
        $user->setConfirmed(true);
        $em->flush();
        $this->executeOperation($user, 'oro_customer_frontend_customeruser_disable');
        self::assertSame($status, $this->client->getResponse()->getStatusCode());
    }

    public function accessGrantedDataProvider(): array
    {
        return [
            'parent customer: DEEP' => [
                'login' => DataFixtures\AbstractLoadACLData::USER_ACCOUNT_1_ROLE_DEEP,
                'resource' => DataFixtures\AbstractLoadACLData::USER_ACCOUNT_1_1_ROLE_LOCAL,
            ],
            'same customer: LOCAL' => [
                'login' => DataFixtures\AbstractLoadACLData::USER_ACCOUNT_1_1_ROLE_LOCAL,
                'resource' => DataFixtures\AbstractLoadACLData::USER_ACCOUNT_1_1_ROLE_DEEP,
            ],
        ];
    }

    public function accessDeniedDataProvider(): array
    {
        return [
            'anonymous user' => [
                'login' => '',
                'resource' => DataFixtures\AbstractLoadACLData::USER_ACCOUNT_1_1_ROLE_LOCAL,
                'status' => Response::HTTP_FORBIDDEN,
            ],
            'same customer: LOCAL_VIEW_ONLY' => [
                'login' => DataFixtures\AbstractLoadACLData::USER_ACCOUNT_1_ROLE_LOCAL_VIEW_ONLY,
                'resource' => DataFixtures\AbstractLoadACLData::USER_ACCOUNT_1_ROLE_LOCAL,
                'status' => Response::HTTP_FORBIDDEN,
            ],
            'parent customer: LOCAL' => [
                'login' => DataFixtures\AbstractLoadACLData::USER_ACCOUNT_1_ROLE_LOCAL,
                'resource' => DataFixtures\AbstractLoadACLData::USER_ACCOUNT_1_1_ROLE_LOCAL,
                'status' => Response::HTTP_FORBIDDEN,
            ],
            'parent customer: DEEP_VIEW_ONLY' => [
                'login' => DataFixtures\AbstractLoadACLData::USER_ACCOUNT_1_ROLE_DEEP_VIEW_ONLY,
                'resource' => DataFixtures\AbstractLoadACLData::USER_ACCOUNT_1_1_ROLE_LOCAL,
                'status' => Response::HTTP_FORBIDDEN,
            ],
        ];
    }

    private function executeOperation(CustomerUser $customerUser, string $operationName): void
    {
        $entityId = $customerUser->getId();
        $entityClass = CustomerUser::class;
        $this->client->request(
            'POST',
            $this->getUrl(
                'oro_frontend_action_operation_execute',
                [
                    'operationName' => $operationName,
                    'route' => 'oro_customer_frontend_customer_user_view',
                    'entityId' => $entityId,
                    'entityClass' => $entityClass,
                ]
            ),
            $this->getOperationExecuteParams($operationName, $entityId, $entityClass),
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );
    }

    private function findCustomerUser(string $resource): CustomerUser
    {
        $repository = self::getContainer()->get('doctrine')
            ->getManagerForClass(CustomerUser::class)
            ->getRepository(CustomerUser::class);

        return $repository->findOneBy(['email' => $resource]);
    }
}
