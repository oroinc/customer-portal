<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Api;

use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\WebsiteBundle\Tests\Functional\Stub\WebsiteManagerStub;

/**
 * The base class for store frontend REST API that conforms JSON.API specification functional tests.
 */
abstract class FrontendRestJsonApiTestCase extends RestJsonApiTestCase
{
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
    protected function getGetRouteName()
    {
        return 'oro_frontend_rest_api_get';
    }

    /**
     * {@inheritdoc}
     */
    protected function getGetListRouteName()
    {
        return 'oro_frontend_rest_api_cget';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDeleteRouteName()
    {
        return 'oro_frontend_rest_api_delete';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDeleteListRouteName()
    {
        return 'oro_frontend_rest_api_cdelete';
    }

    /**
     * {@inheritdoc}
     */
    protected function getPostRouteName()
    {
        return 'oro_frontend_rest_api_post';
    }

    /**
     * {@inheritdoc}
     */
    protected function getPatchRouteName()
    {
        return 'oro_frontend_rest_api_patch';
    }

    /**
     * {@inheritdoc}
     */
    protected function getGetSubresourceRouteName()
    {
        return 'oro_frontend_rest_api_get_subresource';
    }

    /**
     * {@inheritdoc}
     */
    protected function getGetRelationshipRouteName()
    {
        return 'oro_frontend_rest_api_get_relationship';
    }

    /**
     * {@inheritdoc}
     */
    protected function getPatchRelationshipRouteName()
    {
        return 'oro_frontend_rest_api_patch_relationship';
    }

    /**
     * {@inheritdoc}
     */
    protected function getPostRelationshipRouteName()
    {
        return 'oro_frontend_rest_api_post_relationship';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDeleteRelationshipRouteName()
    {
        return 'oro_frontend_rest_api_delete_relationship';
    }

    /**
     * @param string $websiteReference
     */
    public function setCurrentWebsite($websiteReference = null)
    {
        $websiteManagerStub = $this->getWebsiteManagerStub();
        $defaultWebsite = $websiteManagerStub->getDefaultWebsite();
        if (!$websiteReference || $websiteReference === 'default') {
            $website = $defaultWebsite;
        } else {
            if (!$this->hasReference($websiteReference)) {
                throw new \RuntimeException(
                    sprintf('WebsiteScope scope reference "%s" was not found', $websiteReference)
                );
            }
            $website = $this->getReference($websiteReference);
        }

        $websiteManagerStub->enableStub();
        $websiteManagerStub->setCurrentWebsiteStub($website);
        $websiteManagerStub->setDefaultWebsiteStub($defaultWebsite);
    }

    /**
     * @return int
     */
    protected function getDefaultWebsiteId()
    {
        return $this->getWebsiteManagerStub()->getDefaultWebsite()->getId();
    }

    /**
     * @return WebsiteManagerStub
     */
    private function getWebsiteManagerStub()
    {
        $manager = $this->client->getContainer()->get('oro_website.manager');
        if (!$manager instanceof WebsiteManagerStub) {
            throw new \LogicException(sprintf(
                'The service "oro_website.manager" should be instance of "%s", given "%s".',
                WebsiteManagerStub::class,
                get_class($manager)
            ));
        }

        return $manager;
    }
}
