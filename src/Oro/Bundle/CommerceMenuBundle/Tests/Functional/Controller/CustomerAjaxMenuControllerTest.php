<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Functional\Controller;

use Oro\Bundle\CommerceMenuBundle\Tests\Functional\DataFixtures\CustomerMenuUpdateData;
use Oro\Bundle\CommerceMenuBundle\Tests\Functional\DataFixtures\GlobalMenuUpdateData;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomers;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\WebsiteBundle\Tests\Functional\DataFixtures\LoadWebsiteData;
use Symfony\Component\HttpFoundation\Response;

class CustomerAjaxMenuControllerTest extends WebTestCase
{
    const MENU_NAME = 'frontend_menu';

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());

        $this->loadFixtures([GlobalMenuUpdateData::class, CustomerMenuUpdateData::class]);
    }

    public function testCreate()
    {
        $parameters = [
            'menuName' => self::MENU_NAME,
            'parentKey' => GlobalMenuUpdateData::MENU_UPDATE_1,
            'context' => [
                'customer' => $this->getCustomerId(),
                'website' => $this->getWebsiteId()
            ]
        ];

        $this->client->request(
            'POST',
            $this->getUrl('oro_commerce_menu_customer_menu_ajax_create', $parameters),
            [
                'isDivider' => true,
            ]
        );

        $result = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($result, Response::HTTP_CREATED);
    }

    public function testDelete()
    {
        $parameters = [
            'menuName' => self::MENU_NAME,
            'key' => CustomerMenuUpdateData::MENU_UPDATE_1_1,
            'context' => [
                'customer' => $this->getCustomerId(),
                'website' => $this->getWebsiteId()
            ]
        ];

        $this->client->request(
            'DELETE',
            $this->getUrl('oro_commerce_menu_customer_menu_ajax_delete', $parameters),
            ['ownerId' => 0]
        );

        $result = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($result, Response::HTTP_NO_CONTENT);
    }

    public function testShow()
    {
        $parameters = [
            'menuName' => self::MENU_NAME,
            'key' => GlobalMenuUpdateData::MENU_UPDATE_1,
            'context' => [
                'customer' => $this->getCustomerId(),
                'website' => $this->getWebsiteId()
            ]
        ];

        $this->client->request(
            'PUT',
            $this->getUrl('oro_commerce_menu_customer_menu_ajax_show', $parameters),
            ['ownerId' => 0]
        );

        $result = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($result, Response::HTTP_OK);
    }

    public function testHide()
    {
        $parameters = [
            'menuName' => self::MENU_NAME,
            'key' => GlobalMenuUpdateData::MENU_UPDATE_1,
            'context' => [
                'customer' => $this->getCustomerId(),
                'website' => $this->getWebsiteId()
            ]
        ];

        $this->client->request(
            'PUT',
            $this->getUrl('oro_commerce_menu_customer_menu_ajax_hide', $parameters),
            ['ownerId' => 0]
        );

        $result = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($result, Response::HTTP_OK);
    }

    public function testMove()
    {
        $parameters = [
            'menuName' => self::MENU_NAME,
            'context' => [
                'customer' => $this->getCustomerId(),
                'website' => $this->getWebsiteId()
            ]
        ];

        $this->client->request(
            'PUT',
            $this->getUrl('oro_commerce_menu_customer_menu_ajax_move', $parameters),
            [
                'ownerId' => 0,
                'key' => GlobalMenuUpdateData::MENU_UPDATE_1,
                'parentKey' => self::MENU_NAME,
                'position' => 33
            ]
        );

        $result = $this->client->getResponse();

        $this->assertJsonResponseStatusCodeEquals($result, Response::HTTP_OK);
    }

    public function testReset()
    {
        $parameters = [
            'menuName' => self::MENU_NAME,
            'context' => [
                'customer' => $this->getCustomerId(),
                'website' => $this->getWebsiteId()
            ]
        ];

        $this->client->request(
            'DELETE',
            $this->getUrl('oro_commerce_menu_customer_menu_ajax_reset', $parameters),
            ['ownerId' => 0]
        );

        $result = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($result, Response::HTTP_NO_CONTENT);
    }

    /**
     * @return integer
     */
    protected function getCustomerId()
    {
        return $this->getReference(LoadCustomers::CUSTOMER_LEVEL_1_1)->getId();
    }

    /**
     * @return integer
     */
    protected function getWebsiteId()
    {
        return $this->getReference(LoadWebsiteData::WEBSITE1)->getId();
    }
}
