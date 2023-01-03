<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\RestJsonApi;

use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\ApiBundle\ApiDoc\Extractor\CachingApiDocExtractor;
use Oro\Bundle\ApiBundle\Request\ApiAction;
use Oro\Bundle\ApiBundle\Tests\Functional\ApiFeatureTrait;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserApi;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\FrontendTestFrameworkBundle\Test\FrontendWebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @dbIsolationPerTest
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class LoginTest extends FrontendWebTestCase
{
    use ApiFeatureTrait;

    private const JSON_API_MEDIA_TYPE = 'application/vnd.api+json';
    private const JSON_API_CONTENT_TYPE = 'application/vnd.api+json';
    private const API_FEATURE_NAME = 'oro_frontend.web_api';

    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadCustomerUserData::class]);
        $this->setCurrentWebsite();
    }

    private function request(string $method, array $parameters = []): Response
    {
        $server = ['HTTP_ACCEPT' => self::JSON_API_MEDIA_TYPE];
        if ('POST' === $method || 'PATCH' === $method || 'DELETE' === $method) {
            $server['CONTENT_TYPE'] = self::JSON_API_CONTENT_TYPE;
        }

        $this->client->request(
            $method,
            $this->getUrl('oro_frontend_rest_api_list', ['entity' => 'login']),
            $parameters,
            [],
            $server
        );

        $this->getEntityManager()->clear();

        self::assertFalse(
            self::getContainer()->get('oro_api.tests.test_session_listener')->isSessionStarted(),
            'The Session must not be started because REST API is stateless'
        );

        return $this->client->getResponse();
    }

    private function sendLoginRequest(string $email, string $password): Response
    {
        $response = $this->request(
            'POST',
            [
                'meta' => [
                    'email'    => $email,
                    'password' => $password
                ]
            ]
        );

        self::assertResponseContentTypeEquals($response, self::JSON_API_CONTENT_TYPE);

        return $response;
    }

    private function sendOptionsRequest(): Response
    {
        $response = $this->request('OPTIONS');

        self::assertTrue(
            null === self::getContainer()->get('security.token_storage')->getToken(),
            'The security token must not be initialized for OPTIONS request'
        );

        self::assertResponseStatusCodeEquals($response, Response::HTTP_OK);
        self::assertSame('', $response->getContent());
        self::assertSame('0', $response->headers->get('Content-Length'));
        self::assertEquals('max-age=600, public', $response->headers->get('Cache-Control'));
        self::assertEquals('Origin', $response->headers->get('Vary'));

        return $response;
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return self::getContainer()->get('doctrine')->getManager();
    }

    public function testLoginWithEmptyCredentials()
    {
        $response = $this->sendLoginRequest('', '');

        self::assertResponseStatusCodeEquals($response, Response::HTTP_BAD_REQUEST);
        $content = self::jsonToArray($response->getContent());
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
        $content = self::jsonToArray($response->getContent());
        self::assertEquals(
            [
                'errors' => [
                    [
                        'status' => '403',
                        'title'  => 'access denied exception',
                        'detail' => 'The user authentication fails. Reason: Invalid username or password.'
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

        $content = self::jsonToArray($response->getContent());
        self::assertEquals(
            [
                'meta' => [
                    'apiKey' => $user->getApiKeys()->first()->getApiKey()
                ]
            ],
            $content
        );
        self::assertFalse($response->headers->has('Location'), 'Location header');
    }

    public function testLoginWithValidCredentialsAndDisabledApiKeyGeneration()
    {
        $configManager = self::getConfigManager();
        $configManager->set('oro_customer.api_key_generation_enabled', false);

        $response = $this->sendLoginRequest(LoadCustomerUserData::EMAIL, LoadCustomerUserData::PASSWORD);

        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);
        $content = self::jsonToArray($response->getContent());
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

    public function testLoginWithValidCredentialsAndAlreadyGeneratedApiKeyWithCaseSensitive()
    {
        /** @var CustomerUser $user */
        $user = $this->getReference(LoadCustomerUserData::EMAIL);
        $apiKey = new CustomerUserApi();
        $apiKey->setApiKey($apiKey->generateKey());
        $user->addApiKey($apiKey);
        $em = $this->getEntityManager();
        $em->persist($apiKey);
        $em->flush();

        $configManager = self::getConfigManager();
        $configManager->set('oro_customer.case_insensitive_email_addresses_enabled', false);
        $configManager->flush();

        $response = $this->sendLoginRequest(strtoupper(LoadCustomerUserData::EMAIL), LoadCustomerUserData::PASSWORD);

        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);

        $response = $this->sendLoginRequest(LoadCustomerUserData::EMAIL, LoadCustomerUserData::PASSWORD);

        $this->assertCustomerUserLoggedIn($response, $user, $apiKey);
    }

    public function testLoginWithValidCredentialsAndAlreadyGeneratedApiKeyWithCaseInsensitive()
    {
        /** @var CustomerUser $user */
        $user = $this->getReference(LoadCustomerUserData::EMAIL);
        $apiKey = new CustomerUserApi();
        $apiKey->setApiKey($apiKey->generateKey());
        $user->addApiKey($apiKey);
        $em = $this->getEntityManager();
        $em->persist($apiKey);
        $em->flush();

        $configManager = self::getConfigManager();
        $configManager->set('oro_customer.case_insensitive_email_addresses_enabled', true);
        $configManager->flush();

        $response = $this->sendLoginRequest(strtoupper(LoadCustomerUserData::EMAIL), LoadCustomerUserData::PASSWORD);

        $this->assertCustomerUserLoggedIn($response, $user, $apiKey);

        $response = $this->sendLoginRequest(LoadCustomerUserData::EMAIL, LoadCustomerUserData::PASSWORD);

        $this->assertCustomerUserLoggedIn($response, $user, $apiKey);
    }

    private function assertCustomerUserLoggedIn(Response $response, CustomerUser $user, CustomerUserApi $apiKey)
    {
        $existingApiKey = $apiKey->getApiKey();

        self::assertResponseStatusCodeEquals($response, Response::HTTP_OK);
        $content = self::jsonToArray($response->getContent());
        self::assertEquals(
            [
                'meta' => [
                    'apiKey' => $user->getApiKeys()->first()->getApiKey()
                ]
            ],
            $content
        );
        self::assertFalse($response->headers->has('Location'), 'Location header');

        /** @var CustomerUser $user */
        $user = $this->getEntityManager()->find(
            CustomerUser::class,
            $this->getReference(LoadCustomerUserData::EMAIL)->getId()
        );
        self::assertCount(1, $user->getApiKeys());
        self::assertEquals($existingApiKey, $user->getApiKeys()->first()->getApiKey());
    }

    public function testLoginShouldBeAvailableEvenIfGuestsHaveNoAccessToSystem()
    {
        $configManager = self::getConfigManager();
        $configManager->set('oro_frontend.guest_access_enabled', false);
        $configManager->flush();

        $response = $this->sendLoginRequest(LoadCustomerUserData::EMAIL, LoadCustomerUserData::PASSWORD);

        self::assertResponseStatusCodeEquals($response, Response::HTTP_OK);
    }

    public function testLoginForDisabledCustomerUser()
    {
        /** @var CustomerUser $user */
        $user = $this->getReference(LoadCustomerUserData::EMAIL);
        $user->setEnabled(false);
        $this->getEntityManager()->flush();

        $response = $this->sendLoginRequest(LoadCustomerUserData::EMAIL, LoadCustomerUserData::PASSWORD);

        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);
        $content = self::jsonToArray($response->getContent());
        self::assertEquals(
            [
                'errors' => [
                    [
                        'status' => '403',
                        'title'  => 'access denied exception',
                        'detail' => 'The user authentication fails. Reason: Invalid username or password.'
                    ]
                ]
            ],
            $content
        );
    }

    public function testLoginWithWhitespacesInPasswordBeginAndEnd()
    {
        $password = ' test password ';

        /** @var CustomerUser $user */
        $user = $this->getEntityManager()->find(
            CustomerUser::class,
            $this->getReference(LoadCustomerUserData::EMAIL)->getId()
        );
        $user->setPlainPassword($password);

        $userManager = self::getContainer()->get('oro_customer_user.manager');
        $userManager->updatePassword($user);
        $userManager->updateUser($user);

        $response = $this->sendLoginRequest(LoadCustomerUserData::EMAIL, $password);

        self::assertResponseStatusCodeEquals($response, Response::HTTP_OK);

        /** @var CustomerUser $user */
        $user = $this->getEntityManager()->find(
            CustomerUser::class,
            $this->getReference(LoadCustomerUserData::EMAIL)->getId()
        );
        self::assertCount(1, $user->getApiKeys());

        $content = self::jsonToArray($response->getContent());
        self::assertEquals(
            [
                'meta' => [
                    'apiKey' => $user->getApiKeys()->first()->getApiKey()
                ]
            ],
            $content
        );
        self::assertFalse($response->headers->has('Location'), 'Location header');
    }

    public function testOptions()
    {
        $response = $this->sendOptionsRequest();
        self::assertEquals('OPTIONS, POST', $response->headers->get('Allow'));
    }

    /**
     * @dataProvider getNotAllowedMethods
     */
    public function testNotAllowedMethods($method)
    {
        $response = $this->request($method);
        self::assertResponseStatusCodeEquals($response, Response::HTTP_METHOD_NOT_ALLOWED);
        self::assertEquals('OPTIONS, POST', $response->headers->get('Allow'));
    }

    public function getNotAllowedMethods(): array
    {
        return [
            ['HEAD'],
            ['GET'],
            ['PATCH'],
            ['DELETE']
        ];
    }

    public function testOptionsDocumentation()
    {
        $extractor = self::getContainer()->get('nelmio_api_doc.extractor.api_doc_extractor');
        if ($extractor instanceof CachingApiDocExtractor) {
            $extractor->warmUp('frontend_rest_json_api');
        }
        $allDocs = $extractor->all('frontend_rest_json_api');
        $docs = [];
        foreach ($allDocs as $doc) {
            /** @var ApiDoc $annotation */
            $annotation = $doc['annotation'];
            $route = $annotation->getRoute();
            if ($route->getDefault('entity') === 'login'
                && $route->getDefault('_action') === ApiAction::OPTIONS
            ) {
                $docs[] = $doc;
                break;
            }
        }
        self::assertCount(1, $docs);
        $formatter = self::getContainer()->get('nelmio_api_doc.formatter.simple_formatter');
        $data = $formatter->format($docs);
        $resourceData = reset($data);
        $resourceData = reset($resourceData);
        self::assertEquals(
            'Get options',
            $resourceData['description'],
            'description'
        );
        self::assertEquals(
            'Get communication options for a resource',
            $resourceData['documentation'],
            'documentation'
        );
        self::assertTrue(
            empty($resourceData['parameters']),
            'The "parameters" section should be empty'
        );
        self::assertTrue(
            empty($resourceData['filters']),
            'The "filters" section should be empty'
        );
        self::assertTrue(
            empty($resourceData['response']),
            'The "response" section should be empty'
        );
    }

    public function testTryToLoginWithValidCredentialsAndDisabledFeature()
    {
        $this->disableApiFeature(self::API_FEATURE_NAME);
        try {
            $response = $this->request(
                'POST',
                [
                    'meta' => [
                        'email'    => LoadCustomerUserData::EMAIL,
                        'password' => LoadCustomerUserData::PASSWORD
                    ]
                ]
            );
        } finally {
            $this->enableApiFeature(self::API_FEATURE_NAME);
        }

        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }
}
