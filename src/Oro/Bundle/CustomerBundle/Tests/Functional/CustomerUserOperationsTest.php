<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional;

use Oro\Bundle\ActionBundle\Tests\Functional\OperationAwareTestTrait;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Tests\Functional\Controller\EmailMessageAssertionTrait;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserRoleData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mime\Email as SymfonyEmail;

class CustomerUserOperationsTest extends WebTestCase
{
    use EmailMessageAssertionTrait;
    use OperationAwareTestTrait;

    private const EMAIL = LoadCustomerUserData::EMAIL;

    protected function setUp(): void
    {
        $this->initClient([], self::generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
        $this->loadFixtures([LoadCustomerUserRoleData::class]);
    }

    public function testConfirm(): void
    {
        /** @var CustomerUser $user */
        $user = $this->getReference(self::EMAIL);
        self::assertNotNull($user);

        $id = $user->getId();

        $user->setConfirmed(false);
        $em = self::getContainer()->get('doctrine')->getManagerForClass(CustomerUser::class);
        $em->flush();
        $em->clear();

        $this->executeOperation($user, 'oro_customer_customeruser_confirm');

        $emailMessages = self::getMailerMessages();
        self::assertCount(1, $emailMessages);

        /** @var SymfonyEmail $emailMessage */
        $emailMessage = array_shift($emailMessages);

        $this->assertWelcomeMessage($user->getEmail(), $emailMessage);
        self::assertStringContainsString(
            'Please follow the link below to create a password for your new account.',
            $emailMessage->getHtmlBody()
        );

        self::assertJsonResponseStatusCodeEquals($this->client->getResponse(), 200);

        $user = $em->getRepository(CustomerUser::class)->find($id);

        self::assertNotNull($user);
        self::assertTrue($user->isConfirmed());
    }

    public function testSendConfirmation(): void
    {
        /** @var CustomerUser $user */
        $user = $this->getReference(self::EMAIL);
        $user->setConfirmed(false);
        $em = self::getContainer()->get('doctrine')->getManagerForClass(CustomerUser::class);
        $em->flush();
        $em->clear();

        $this->executeOperation($user, 'oro_customer_customeruser_sendconfirmation');

        $result = $this->client->getResponse();
        self::assertJsonResponseStatusCodeEquals($result, 200);

        $emailMessages = self::getMailerMessages();
        self::assertCount(1, $emailMessages);

        /** @var SymfonyEmail $emailMessage */
        $emailMessage = array_shift($emailMessages);

        self::assertInstanceOf(SymfonyEmail::class, $emailMessage);
        self::assertEmailAddressContains($emailMessage, 'to', self::EMAIL);
        self::assertStringContainsString('Confirmation of account registration', $emailMessage->getSubject());
        self::assertStringContainsString(self::EMAIL, $emailMessage->getHtmlBody());
    }

    public function testEnableAndDisable(): void
    {
        $em = self::getContainer()->get('doctrine')->getManagerForClass(CustomerUser::class);
        $repository = $em->getRepository(CustomerUser::class);

        /** @var CustomerUser $user */
        $user = $repository->findOneBy(['email' => self::EMAIL]);
        $id = $user->getId();

        self::assertNotNull($user);
        self::assertTrue($user->isEnabled());

        $this->executeOperation($user, 'oro_customer_customeruser_disable');
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());

        $em->clear();

        $user = $repository->find($id);
        self::assertFalse($user->isEnabled());
        self::assertNotEmpty($user->getUserRoles());

        $this->executeOperation($user, 'oro_customer_customeruser_enable');
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());

        $em->clear();

        $user = $repository->find($id);
        self::assertTrue($user->isEnabled());
    }

    private function executeOperation(CustomerUser $customerUser, string $operationName): void
    {
        $entityId = $customerUser->getId();
        $entityClass = CustomerUser::class;
        $this->client->request(
            'POST',
            $this->getUrl(
                'oro_action_operation_execute',
                [
                    'operationName' => $operationName,
                    'route' => 'oro_customer_customer_user_view',
                    'entityId' => $entityId,
                    'entityClass' => $entityClass,
                ]
            ),
            $this->getOperationExecuteParams($operationName, $entityId, $entityClass)
        );
    }
}
