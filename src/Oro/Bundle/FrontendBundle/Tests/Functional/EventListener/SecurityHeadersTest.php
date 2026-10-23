<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Functional\EventListener;

use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadAddressBookUserData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests that storefront error and redirect responses have the same security headers as a regular page.
 */
final class SecurityHeadersTest extends WebTestCase
{
    use ConfigManagerAwareTestTrait;

    private const array SECURITY_HEADERS = ['X-Frame-Options', 'X-Content-Type-Options', 'X-XSS-Protection'];

    #[\Override]
    protected function setUp(): void
    {
        $this->initClient();
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->restoreConfigValues();
        parent::tearDown();
    }

    private function getRegularPageSecurityHeaders(): array
    {
        $this->client->request('GET', $this->getUrl('oro_customer_customer_user_security_login'));
        $response = $this->client->getResponse();
        self::assertHtmlResponseStatusCodeEquals($response, Response::HTTP_OK);
        $headers = $this->getSecurityHeaders($response);
        foreach ($headers as $name => $value) {
            self::assertNotNull($value, sprintf('The "%s" header is missing.', $name));
        }

        return $headers;
    }

    private function getSecurityHeaders(Response $response): array
    {
        $headers = [];
        foreach (self::SECURITY_HEADERS as $name) {
            $headers[$name] = $response->headers->get($name);
        }

        return $headers;
    }

    public function testNotFoundPage(): void
    {
        $expectedHeaders = $this->getRegularPageSecurityHeaders();

        $this->client->request('GET', '/non-existing-url-bb27956');
        $response = $this->client->getResponse();

        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
        self::assertSame($expectedHeaders, $this->getSecurityHeaders($response));
    }

    public function testForbiddenPage(): void
    {
        $this->loadFixtures([LoadAddressBookUserData::class]);
        $expectedHeaders = $this->getRegularPageSecurityHeaders();

        $this->loginUser(LoadAddressBookUserData::ACCOUNT1_USER4);
        try {
            $this->client->request('GET', $this->getUrl('oro_customer_frontend_customer_user_address_index'));
            $response = $this->client->getResponse();
        } finally {
            $this->loginUser('');
        }

        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);
        self::assertSame($expectedHeaders, $this->getSecurityHeaders($response));
    }

    public function testRedirectToLoginPageWhenGuestAccessIsDisabled(): void
    {
        $expectedHeaders = $this->getRegularPageSecurityHeaders();

        $this->setConfigValue('oro_frontend.guest_access_enabled', false);
        $this->client->request('GET', '/');
        $response = $this->client->getResponse();

        self::assertResponseStatusCodeEquals($response, Response::HTTP_FOUND);
        self::assertStringEndsWith(
            $this->getUrl('oro_customer_customer_user_security_login'),
            $response->headers->get('Location')
        );
        self::assertSame($expectedHeaders, $this->getSecurityHeaders($response));
    }
}
