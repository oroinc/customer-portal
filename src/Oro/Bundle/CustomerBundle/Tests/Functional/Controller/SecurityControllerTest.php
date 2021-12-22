<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller;

use Oro\Bundle\FrontendTestFrameworkBundle\Migrations\Data\ORM\LoadCustomerUserData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @dbIsolationPerTest
 */
class SecurityControllerTest extends WebTestCase
{
    public function testLoginAction()
    {
        $this->initClient();

        $crawler = $this->client->request('GET', $this->getUrl('oro_customer_customer_user_security_login'));

        $response = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($response, 200);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertCount(1, $crawler->filter('form#form-login'));
    }

    public function testLoginActionWithLoggedUser()
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader(LoadCustomerUserData::AUTH_USER, LoadCustomerUserData::AUTH_PW)
        );

        $this->client->request('GET', $this->getUrl('oro_customer_customer_user_security_login'));

        /* @var RedirectResponse $response */
        $response = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($response, 302);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(
            $this->getContainer()->get('router')->generate('oro_customer_frontend_customer_user_profile'),
            $response->getTargetUrl()
        );
    }

    public function testLoginActionWithAjax()
    {
        $this->initClient();

        $this->client->request(
            'GET',
            $this->getUrl('oro_customer_customer_user_security_login'),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        /* @var JsonResponse $response */
        $response = $this->client->getResponse();

        $this->assertJsonResponseStatusCodeEquals($response, 401);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $redirectUrl = $this->getContainer()->get('router')->generate('oro_customer_customer_user_security_login');
        $this->assertEquals(
            [
                'redirectUrl' => $redirectUrl,
            ],
            json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR)
        );
    }
}
