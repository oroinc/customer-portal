<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Api;

use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\FrontendTestFrameworkBundle\Test\WebsiteManagerTrait;

/**
 * The base class for store frontend REST API that conforms JSON.API specification functional tests.
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
    protected function setUp()
    {
        parent::setUp();
        $this->setCurrentWebsite();
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
    protected function getRequestType()
    {
        $requestType = parent::getRequestType();
        $requestType->add('frontend');

        return $requestType;
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
}
