<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Api;

use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Security\Firewall\AnonymousCustomerUserAuthenticationListener;
use Oro\Bundle\FrontendTestFrameworkBundle\Test\WebsiteManagerTrait;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Routing\RequestContext;

/**
 * The base class for store frontend REST API that conforms JSON:API specification functional tests.
 */
abstract class FrontendRestJsonApiTestCase extends RestJsonApiTestCase
{
    use WebsiteManagerTrait;

    /** Default WSSE credentials */
    const USER_NAME     = 'frontend_admin_api@example.com';
    const USER_PASSWORD = 'frontend_admin_api_key';

    /**
     * {@inheritdoc}
     */
    protected function assertPreConditions()
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
     * @after
     */
    public function afterFrontendTest()
    {
        if (null !== $this->client) {
            $this->getWebsiteManagerStub()->disableStub();
        }
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
    protected function getRequestType()
    {
        $requestType = parent::getRequestType();
        $requestType->add('frontend');

        return $requestType;
    }

    /**
     * @param CustomerVisitor $visitor
     */
    protected function setVisitorCookie(CustomerVisitor $visitor)
    {
        $value = base64_encode(json_encode([$visitor->getId(), $visitor->getSessionId()]));
        $this->client->getCookieJar()->set(
            new Cookie(AnonymousCustomerUserAuthenticationListener::COOKIE_NAME, $value)
        );
    }

    /**
     * @return array
     */
    protected function getWsseAuthHeader()
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
    protected function getItemRouteName()
    {
        return 'oro_frontend_rest_api_item';
    }

    /**
     * {@inheritdoc}
     */
    protected function getListRouteName()
    {
        return 'oro_frontend_rest_api_list';
    }

    /**
     * {@inheritdoc}
     */
    protected function getSubresourceRouteName()
    {
        return 'oro_frontend_rest_api_subresource';
    }

    /**
     * {@inheritdoc}
     */
    protected function getRelationshipRouteName()
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
