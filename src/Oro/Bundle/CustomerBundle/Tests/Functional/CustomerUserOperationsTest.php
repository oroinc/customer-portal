<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Tests\Functional\Controller\EmailMessageAssertionTrait;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CustomerUserOperationsTest extends WebTestCase
{
    use EmailMessageAssertionTrait;

    const EMAIL = LoadCustomerUserData::EMAIL;

    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
        $this->loadFixtures(
            [
                'Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserRoleData'
            ]
        );
    }

    public function testConfirm()
    {
        /** @var CustomerUser $user */
        $user = $this->getReference(static::EMAIL);
        $this->assertNotNull($user);

        $id = $user->getId();

        $user->setConfirmed(false);
        $em = $this->getContainer()->get('doctrine')->getManagerForClass(CustomerUser::class);
        $em->flush();
        $em->clear();

        $this->executeOperation($user, 'oro_customer_customeruser_confirm');

        /** @var \Swift_Plugins_MessageLogger $emailLogging */
        $emailLogger = $this->getContainer()->get('swiftmailer.plugin.messagelogger');
        $emailMessages = $emailLogger->getMessages();

        $this->assertCount(1, $emailMessages);

        /** @var \Swift_Message $emailMessage */
        $emailMessage = array_shift($emailMessages);
        $this->assertWelcomeMessage($user->getEmail(), $emailMessage);
        static::assertStringContainsString(
            'Please follow the link below to create a password for your new account.',
            $emailMessage->getBody()
        );

        $this->assertJsonResponseStatusCodeEquals($this->client->getResponse(), 200);

        $user = $em->getRepository(CustomerUser::class)->find($id);

        $this->assertNotNull($user);
        $this->assertTrue($user->isConfirmed());
    }

    public function testSendConfirmation()
    {
        /** @var CustomerUser $user */
        $email = static::EMAIL;

        $user = $this->getReference($email);
        $user->setConfirmed(false);
        $em = $this->getContainer()->get('doctrine')->getManagerForClass(CustomerUser::class);
        $em->flush();
        $em->clear();

        $this->executeOperation($user, 'oro_customer_customeruser_sendconfirmation');

        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 200);

        /** @var \Swift_Plugins_MessageLogger $emailLogging */
        $emailLogger = $this->getContainer()->get('swiftmailer.plugin.messagelogger');
        $emailMessages = $emailLogger->getMessages();

        /** @var \Swift_Message $message */
        $message = reset($emailMessages);

        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertEquals($email, key($message->getTo()));
        static::assertStringContainsString('Confirmation of account registration', $message->getSubject());
        static::assertStringContainsString($email, $message->getBody());
    }

    public function testEnableAndDisable()
    {
        $em = $this->getContainer()->get('doctrine')->getManagerForClass(CustomerUser::class);
        $repository = $em->getRepository(CustomerUser::class);

        /** @var CustomerUser $user */
        $user = $repository->findOneBy(['email' => static::EMAIL]);
        $id = $user->getId();

        $this->assertNotNull($user);
        $this->assertTrue($user->isEnabled());

        $this->executeOperation($user, 'oro_customer_customeruser_disable');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $em->clear();

        $user = $repository->find($id);
        $this->assertFalse($user->isEnabled());
        $this->assertNotEmpty($user->getRoles());

        $this->executeOperation($user, 'oro_customer_customeruser_enable');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $em->clear();

        $user = $repository->find($id);
        $this->assertTrue($user->isEnabled());
    }

    /**
     * {@inheritdoc}
     */
    protected function executeOperation(CustomerUser $customerUser, $operationName)
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
                    'entityClass' => $entityClass
                ]
            ),
            $this->getOperationExecuteParams($operationName, $entityId, $entityClass)
        );
    }

    /**
     * @param $operationName
     * @param $entityId
     * @param $entityClass
     *
     * @return array
     */
    protected function getOperationExecuteParams($operationName, $entityId, $entityClass)
    {
        $actionContext = [
            'entityId'    => $entityId,
            'entityClass' => $entityClass
        ];
        $container = self::getContainer();
        $operation = $container->get('oro_action.operation_registry')->findByName($operationName);
        $actionData = $container->get('oro_action.helper.context')->getActionData($actionContext);

        $tokenData = $container
            ->get('oro_action.operation.execution.form_provider')
            ->createTokenData($operation, $actionData);
        $container->get('session')->save();

        return $tokenData;
    }
}
