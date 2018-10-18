<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Tests\Functional\Controller\EmailMessageAssertionTrait;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserACLData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @dbIsolationPerTest
 */
class CustomerUserFrontendOperationsTest extends WebTestCase
{
    use EmailMessageAssertionTrait;

    protected function setUp()
    {
        $this->initClient();
        $this->client->useHashNavigation(true);
        $this->loadFixtures(
            [
                LoadCustomerUserACLData::class
            ]
        );
    }

    /**
     * @dataProvider accessGrantedDataProvider
     *
     * @param string $login
     * @param string $resource
     */
    public function testSendConfirmation($login, $resource)
    {
        $em = $this->getContainer()->get('doctrine')
            ->getManagerForClass(CustomerUser::class);
        $this->loginUser($login);

        $user = $this->findCustomerUser($resource);

        $user->setConfirmed(false);
        $em->flush();

        $this->executeOperation($user, 'oro_customer_customeruser_sendconfirmation');

        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, Response::HTTP_OK);

        /** @var \Swift_Plugins_MessageLogger $emailLogging */
        $emailLogger = $this->getContainer()->get('swiftmailer.plugin.messagelogger');
        $emailMessages = $emailLogger->getMessages();

        /** @var \Swift_Message $message */
        $message = reset($emailMessages);

        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertEquals($resource, key($message->getTo()));
        $this->assertContains('Confirmation of account registration', $message->getSubject());
        $this->assertContains($resource, $message->getBody());

        $user = $this->findCustomerUser($resource);
        $user->setConfirmed(true);
        $em->flush();
    }

    /**
     * @dataProvider accessDeniedDataProvider
     *
     * @param string $login
     * @param string $resource
     * @param int $status
     */
    public function testSendConfirmationAccessDenied($login, $resource, $status)
    {
        $em = $this->getContainer()->get('doctrine')
            ->getManagerForClass(CustomerUser::class);
        $this->loginUser($login);

        /** @var CustomerUser $user */
        $user = $this->findCustomerUser($resource);
        $user->setConfirmed(false);
        $em->flush();

        $this->client->getContainer()->get('doctrine')->getManager()->clear();

        $this->executeOperation($user, 'oro_customer_customeruser_sendconfirmation');
        $this->assertSame($status, $this->client->getResponse()->getStatusCode());

        $user = $this->findCustomerUser($resource);
        $user->setConfirmed(true);
        $em->flush();
    }

    /**
     * @dataProvider accessGrantedDataProvider
     *
     * @param string $login
     * @param string $resource
     */
    public function testConfirmAccessGranted($login, $resource)
    {
        $em = $this->getContainer()->get('doctrine')->getManagerForClass(CustomerUser::class);
        $this->loginUser($login);

        /** @var \Oro\Bundle\CustomerBundle\Entity\CustomerUser $user */
        $user = $this->findCustomerUser($resource);
        $user->setConfirmed(false);
        $em->flush();

        $this->executeOperation($user, 'oro_customer_customeruser_confirm');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $user = $this->findCustomerUser($resource);
        $this->assertTrue($user->isConfirmed());

        /** @var \Swift_Plugins_MessageLogger $emailLogging */
        $emailLogger = $this->getContainer()->get('swiftmailer.plugin.messagelogger');
        $emailMessages = $emailLogger->getMessages();

        $this->assertCount(1, $emailMessages);

        /** @var \Swift_Message $emailMessage */
        $emailMessage = array_shift($emailMessages);
        $this->assertWelcomeMessage($user->getEmail(), $emailMessage);
        $this->assertContains(
            'Please follow the link below to create a password for your new account.',
            $emailMessage->getBody()
        );

        $user = $this->findCustomerUser($resource);
        $this->assertTrue($user->isConfirmed());
    }

    /**
     * @dataProvider accessDeniedDataProvider
     *
     * @param string $login
     * @param string $resource
     * @param int $status
     */
    public function testConfirmAccessDenied($login, $resource, $status)
    {
        $em = $this->getContainer()->get('doctrine')->getManagerForClass(CustomerUser::class);
        $this->loginUser($login);

        /** @var CustomerUser $user */
        $user = $this->findCustomerUser($resource);

        $user->setConfirmed(false);
        $em->flush();

        $this->executeOperation($user, 'oro_customer_customeruser_confirm');
        $this->assertSame($status, $this->client->getResponse()->getStatusCode());

        $user = $this->findCustomerUser($resource);
        $user->setConfirmed(true);
        $em->flush();
    }

    /**
     * @dataProvider accessGrantedDataProvider
     *
     * @param string $login
     * @param string $resource
     */
    public function testEnableAndDisable($login, $resource)
    {
        $em = $this->getContainer()->get('doctrine')->getManagerForClass(CustomerUser::class);
        $this->loginUser($login);

        $user = $this->findCustomerUser($resource);
        $this->assertTrue($user->isEnabled());

        $this->executeOperation($user, 'oro_customer_frontend_customeruser_disable');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $user = $this->findCustomerUser($resource);
        $this->assertFalse($user->isEnabled());
        $this->assertNotEmpty($user->getRoles());
        $this->executeOperation($user, 'oro_customer_frontend_customeruser_enable');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $user = $this->findCustomerUser($resource);
        $this->assertTrue($user->isEnabled());

        $user = $this->findCustomerUser($resource);
        $user->setConfirmed(true);
        $em->flush();
    }

    /**
     * @dataProvider accessDeniedDataProvider
     *
     * @param string $login
     * @param string $resource
     * @param int $status
     */
    public function testEnableAndDisableAccessDenied($login, $resource, $status)
    {
        $em = $this->getContainer()->get('doctrine')->getManagerForClass(CustomerUser::class);
        $this->loginUser($login);

        $user = $this->findCustomerUser($resource);
        $user->setConfirmed(false);
        $em->flush();
        $this->executeOperation($user, 'oro_customer_frontend_customeruser_enable');
        $this->assertSame($status, $this->client->getResponse()->getStatusCode());

        $user = $this->findCustomerUser($resource);
        $user->setConfirmed(true);
        $em->flush();
        $this->executeOperation($user, 'oro_customer_frontend_customeruser_disable');
        $this->assertSame($status, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @return array
     */
    public function accessGrantedDataProvider()
    {
        return [
            'parent customer: DEEP' => [
                'login' => LoadCustomerUserACLData::USER_ACCOUNT_1_ROLE_DEEP,
                'resource' => LoadCustomerUserACLData::USER_ACCOUNT_1_1_ROLE_LOCAL,
            ],
            'same customer: LOCAL' => [
                'login' => LoadCustomerUserACLData::USER_ACCOUNT_1_1_ROLE_LOCAL,
                'resource' => LoadCustomerUserACLData::USER_ACCOUNT_1_1_ROLE_DEEP,
            ],
        ];
    }

    /**
     * @return array
     */
    public function accessDeniedDataProvider()
    {
        return [
            'anonymous user' => [
                'login' => '',
                'resource' => LoadCustomerUserACLData::USER_ACCOUNT_1_1_ROLE_LOCAL,
                'status' => Response::HTTP_FORBIDDEN,
            ],
            'same customer: LOCAL_VIEW_ONLY' => [
                'login' => LoadCustomerUserACLData::USER_ACCOUNT_1_ROLE_LOCAL_VIEW_ONLY,
                'resource' => LoadCustomerUserACLData::USER_ACCOUNT_1_ROLE_LOCAL,
                'status' => Response::HTTP_FORBIDDEN,
            ],
            'parent customer: LOCAL' => [
                'login' => LoadCustomerUserACLData::USER_ACCOUNT_1_ROLE_LOCAL,
                'resource' => LoadCustomerUserACLData::USER_ACCOUNT_1_1_ROLE_LOCAL,
                'status' => Response::HTTP_FORBIDDEN,
            ],
            'parent customer: DEEP_VIEW_ONLY' => [
                'login' => LoadCustomerUserACLData::USER_ACCOUNT_1_ROLE_DEEP_VIEW_ONLY,
                'resource' => LoadCustomerUserACLData::USER_ACCOUNT_1_1_ROLE_LOCAL,
                'status' => Response::HTTP_FORBIDDEN,
            ],
        ];
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
                'oro_frontend_action_operation_execute',
                [
                    'operationName' => $operationName,
                    'route' => 'oro_customer_frontend_customer_user_view',
                    'entityId' => $entityId,
                    'entityClass' => $entityClass
                ]
            ),
            $this->getOperationExecuteParams($operationName, $entityId, $entityClass),
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
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

    /**
     * @param string $resource
     * @return CustomerUser
     */
    private function findCustomerUser($resource): CustomerUser
    {
        $repository = $this->getContainer()->get('doctrine')
            ->getManagerForClass(CustomerUser::class)
            ->getRepository(CustomerUser::class);

        return $repository->findOneBy(['email' => $resource]);
    }
}
