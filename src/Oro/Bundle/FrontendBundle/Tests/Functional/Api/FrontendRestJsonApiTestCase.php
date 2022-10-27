<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Api;

use Oro\Bundle\ApiBundle\Request\RequestType;
use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Security\Firewall\AnonymousCustomerUserAuthenticationListener;
use Oro\Bundle\FrontendTestFrameworkBundle\Test\WebsiteManagerTrait;
use Oro\Bundle\SecurityBundle\Csrf\CsrfRequestManager;
use Oro\Bundle\TestFrameworkBundle\Test\Client;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Routing\RequestContext;

/**
 * The base class for store frontend REST API that conforms the JSON:API specification functional tests.
 */
abstract class FrontendRestJsonApiTestCase extends RestJsonApiTestCase
{
    use WebsiteManagerTrait;

    /** Default WSSE credentials */
    protected const USER_NAME = 'frontend_admin_api@example.com';
    protected const USER_PASSWORD = 'frontend_admin_api_key';

    private bool $isVisitorEnabled = false;

    /**
     * Enables an authorization as a visitor.
     */
    protected function enableVisitor(): void
    {
        $this->isVisitorEnabled = true;
    }

    /**
     * Disables an authorization as a visitor.
     */
    protected function disableVisitor(): void
    {
        $this->isVisitorEnabled = false;
    }

    protected function assertPreConditions(): void
    {
        parent::assertPreConditions();
        // set the current website after all fixtures are loaded,
        // to make sure that its the ORM state is "managed".
        // if fixtures are loaded in a test method, not in setUp() method,
        // the setCurrentWebsite() method need to be called manually in this test method
        if (null !== $this->client) {
            $this->setCurrentWebsite();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function postFixtureLoad()
    {
        parent::postFixtureLoad();
        if ($this->isVisitorEnabled
            && (
                !$this->hasReference('customer_user')
                || $this->getReference('customer_user')->getEmail() !== static::USER_NAME
            )
        ) {
            $this->loadVisitor();
        }
    }

    /**
     * Creates a visitor and adds it to cookies to execute API requests under this visitor.
     */
    protected function loadVisitor(): void
    {
        $this->assertVisitorEnabled();

        if (null !== $this->client->getCookieJar()->get(AnonymousCustomerUserAuthenticationListener::COOKIE_NAME)) {
            return;
        }

        $visitor = new CustomerVisitor();
        $em = $this->getEntityManager();
        $em->persist($visitor);
        $em->flush();
        $this->setVisitorCookie($visitor);
    }

    /**
     * @beforeResetClient
     */
    public static function afterFrontendTest(): void
    {
        self::getWebsiteManagerStub()->disableStub();
    }

    /**
     * {@inheritdoc}
     */
    protected function getListenersThatShouldBeDisabledDuringDataFixturesLoading()
    {
        $listeners = parent::getListenersThatShouldBeDisabledDuringDataFixturesLoading();
        if (self::getContainer()->has('oro_sales.customers.customer_association_listener')) {
            $listeners[] = 'oro_sales.customers.customer_association_listener';
        }

        return $listeners;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequestType(): RequestType
    {
        $requestType = parent::getRequestType();
        $requestType->add('frontend');

        return $requestType;
    }

    protected function assertVisitorEnabled(): void
    {
        if (!$this->isVisitorEnabled) {
            throw new \LogicException('An authorization as a visitor is disabled. Call enableVisitor() method before');
        }
    }

    protected function setVisitorCookie(CustomerVisitor $visitor): void
    {
        $this->assertVisitorEnabled();

        $cookieJar = $this->client->getCookieJar();
        $cookieJar->set(new Cookie(
            AnonymousCustomerUserAuthenticationListener::COOKIE_NAME,
            base64_encode(json_encode([$visitor->getId(), $visitor->getSessionId()], JSON_THROW_ON_ERROR))
        ));
        // set "_csrf" cookie with domain to be sure it was rewritten after previous request
        $domain = str_replace('http://', '', Client::LOCAL_URL);
        $cookieJar->set(new Cookie(CsrfRequestManager::CSRF_TOKEN_ID, 'test_csrf_token', null, null, $domain));
        // a marker for a stateful test API request
        $cookieJar->set(new Cookie(self::API_TEST_STATEFUL_REQUEST, 'visitor', null, null, $domain));
    }

    /**
     * {@inheritdoc}
     */
    protected function getWsseAuthHeader(): array
    {
        /**
         * WSSE header should be generated only if the customer user (an user with the email
         * equal to static::USER_NAME) exists in the database, it means that it must be loaded
         * by a data fixture in your test class, usually in "setUp()" method.
         * The reason for this is that the frontend API can be executed by both
         * the customer user and the anonymous user.
         */
        $customerUser = $this->getEntityManager()
            ->getRepository(CustomerUser::class)
            ->findBy(['email' => static::USER_NAME]);
        if (!$customerUser) {
            return [];
        }

        return parent::getWsseAuthHeader();
    }

    /**
     * {@inheritdoc}
     */
    protected function getItemRouteName(): string
    {
        return 'oro_frontend_rest_api_item';
    }

    /**
     * {@inheritdoc}
     */
    protected function getListRouteName(): string
    {
        return 'oro_frontend_rest_api_list';
    }

    /**
     * {@inheritdoc}
     */
    protected function getSubresourceRouteName(): string
    {
        return 'oro_frontend_rest_api_subresource';
    }

    /**
     * {@inheritdoc}
     */
    protected function getRelationshipRouteName(): string
    {
        return 'oro_frontend_rest_api_relationship';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUrl($name, $parameters = [], $absolute = false)
    {
        // substitute the path info to avoid unnecessary usage of slugs
        // the '/api/' is one of the skipped patterns for the slug decision maker
        /** @var RequestContext $requestContext */
        $requestContext = self::getContainer()->get('router.request_context');
        $pathInfo = $requestContext->getPathInfo();
        if ('/' === $pathInfo) {
            $requestContext->setPathInfo('/api/');
        }
        try {
            return parent::getUrl($name, $parameters, $absolute);
        } finally {
            $requestContext->setPathInfo($pathInfo);
        }
    }
}
