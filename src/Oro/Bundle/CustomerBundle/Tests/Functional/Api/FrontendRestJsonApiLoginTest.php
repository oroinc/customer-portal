<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserApi;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\FrontendTestFrameworkBundle\Test\FrontendWebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @dbIsolationPerTest
 */
class FrontendRestJsonApiLoginTest extends FrontendWebTestCase
{
    const JSON_API_CONTENT_TYPE = 'application/vnd.api+json';

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->initClient();
        $this->setCurrentWebsite();
        $this->loadFixtures([LoadCustomerUserData::class]);
    }

    /**
     * @param string $email
     * @param string $password
     *
     * @return Response
     */
    private function sendLoginRequest($email, $password)
    {
        $this->client->request(
            'POST',
            $this->getUrl('oro_frontend_rest_api_post', ['entity' => 'login']),
            [
                'meta' => [
                    'email'    => $email,
                    'password' => $password
                ]
            ],
            [],
            ['CONTENT_TYPE' => self::JSON_API_CONTENT_TYPE]
        );

        $this->getEntityManager()->clear();

        $response = $this->client->getResponse();
        self::assertResponseContentTypeEquals($response, self::JSON_API_CONTENT_TYPE);

        return $response;
    }

    /**
     * @return EntityManagerInterface
     */
    private function getEntityManager()
    {
        return self::getContainer()->get('doctrine')->getManager();
    }

    public function testLoginWithEmptyCredentials()
    {
        $response = $this->sendLoginRequest('', '');

        self::assertResponseStatusCodeEquals($response, Response::HTTP_BAD_REQUEST);
        $content = json_decode($response->getContent(), true);
        self::assertEquals(
            [
                'errors' => [
                    [
                        'status' => '400',
                        'title'  => 'not blank constraint',
                        'detail' => 'This value should not be blank.',
                        'source' => ['pointer' => '/meta/email']
                    ],
                    [
                        'status' => '400',
                        'title'  => 'not blank constraint',
                        'detail' => 'This value should not be blank.',
                        'source' => ['pointer' => '/meta/password']
                    ]
                ]
            ],
            $content
        );
    }

    public function testLoginWithInvalidCredentials()
    {
        $response = $this->sendLoginRequest('unknown@example.com', 'unknown');

        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);
        $content = json_decode($response->getContent(), true);
        self::assertEquals(
            [
                'errors' => [
                    [
                        'status' => '403',
                        'title'  => 'access denied exception',
                        'detail' => 'The user authentication fails. Reason: Invalid user name or password.'
                    ]
                ]
            ],
            $content
        );
    }

    public function testLoginWithValidCredentialsAndEnabledApiKeyGeneration()
    {
        $response = $this->sendLoginRequest(LoadCustomerUserData::EMAIL, LoadCustomerUserData::PASSWORD);

        self::assertResponseStatusCodeEquals($response, Response::HTTP_OK);

        /** @var CustomerUser $user */
        $user = $this->getEntityManager()->find(
            CustomerUser::class,
            $this->getReference(LoadCustomerUserData::EMAIL)->getId()
        );
        self::assertCount(1, $user->getApiKeys());

        $content = json_decode($response->getContent(), true);
        self::assertEquals(
            [
                'meta' => [
                    'apiKey' => $user->getApiKeys()->first()->getApiKey()
                ]
            ],
            $content
        );
    }

    public function testLoginWithValidCredentialsAndDisabledApiKeyGeneration()
    {
        /** @var ConfigManager $configManager */
        $configManager = self::getContainer()->get('oro_config.manager');
        $configManager->set('oro_customer.api_key_generation_enabled', false);

        $response = $this->sendLoginRequest(LoadCustomerUserData::EMAIL, LoadCustomerUserData::PASSWORD);

        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);
        $content = json_decode($response->getContent(), true);
        self::assertEquals(
            [
                'errors' => [
                    [
                        'status' => '403',
                        'title'  => 'access denied exception',
                        'detail' => 'The API access key was not generated for this user.'
                    ]
                ]
            ],
            $content
        );

        /** @var CustomerUser $user */
        $user = $this->getEntityManager()->find(
            CustomerUser::class,
            $this->getReference(LoadCustomerUserData::EMAIL)->getId()
        );
        self::assertCount(0, $user->getApiKeys());
    }

    public function testLoginWithValidCredentialsAndAlreadyGeneratedApiKey()
    {
        /** @var CustomerUser $user */
        $user = $this->getReference(LoadCustomerUserData::EMAIL);
        $apiKey = new CustomerUserApi();
        $apiKey->setApiKey($apiKey->generateKey());
        $user->addApiKey($apiKey);
        $em = $this->getEntityManager();
        $em->persist($apiKey);
        $em->flush();

        $existingApiKey = $apiKey->getApiKey();

        $response = $this->sendLoginRequest(LoadCustomerUserData::EMAIL, LoadCustomerUserData::PASSWORD);

        self::assertResponseStatusCodeEquals($response, Response::HTTP_OK);
        $content = json_decode($response->getContent(), true);
        self::assertEquals(
            [
                'meta' => [
                    'apiKey' => $user->getApiKeys()->first()->getApiKey()
                ]
            ],
            $content
        );

        /** @var CustomerUser $user */
        $user = $this->getEntityManager()->find(
            CustomerUser::class,
            $this->getReference(LoadCustomerUserData::EMAIL)->getId()
        );
        self::assertCount(1, $user->getApiKeys());
        self::assertEquals($existingApiKey, $user->getApiKeys()->first()->getApiKey());
    }

    public function testLoginForDisabledCustomerUser()
    {
        /** @var CustomerUser $user */
        $user = $this->getReference(LoadCustomerUserData::EMAIL);
        $user->setEnabled(false);
        $this->getEntityManager()->flush();

        $response = $this->sendLoginRequest(LoadCustomerUserData::EMAIL, LoadCustomerUserData::PASSWORD);

        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);
        $content = json_decode($response->getContent(), true);
        self::assertEquals(
            [
                'errors' => [
                    [
                        'status' => '403',
                        'title'  => 'access denied exception',
                        'detail' => 'The user authentication fails. Reason: Account is locked.'
                    ]
                ]
            ],
            $content
        );
    }
}
