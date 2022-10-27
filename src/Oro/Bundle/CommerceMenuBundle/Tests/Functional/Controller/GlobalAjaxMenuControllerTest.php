<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Functional\Controller;

use Oro\Bundle\CommerceMenuBundle\Tests\Functional\DataFixtures\GlobalMenuUpdateData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class GlobalAjaxMenuControllerTest extends WebTestCase
{
    private const MENU_NAME = 'frontend_menu';

    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->loadFixtures([GlobalMenuUpdateData::class]);
    }

    public function testCreate()
    {
        $parameters = [
            'menuName' => self::MENU_NAME,
            'parentKey' => GlobalMenuUpdateData::MENU_UPDATE_1,
        ];

        $this->ajaxRequest(
            'POST',
            $this->getUrl('oro_commerce_menu_global_menu_ajax_create', $parameters),
            [
                'isDivider' => true
            ]
        );

        $result = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($result, Response::HTTP_CREATED);
    }

    public function testDelete()
    {
        $parameters = [
            'menuName' => self::MENU_NAME,
            'key' => GlobalMenuUpdateData::MENU_UPDATE_1
        ];

        $this->ajaxRequest(
            'DELETE',
            $this->getUrl('oro_commerce_menu_global_menu_ajax_delete', $parameters),
            ['ownerId' => 0]
        );

        $result = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($result, Response::HTTP_NO_CONTENT);
    }

    public function testShow()
    {
        $parameters = [
            'menuName' => self::MENU_NAME,
            'key' => GlobalMenuUpdateData::MENU_UPDATE_1
        ];

        $this->ajaxRequest(
            'PUT',
            $this->getUrl('oro_commerce_menu_global_menu_ajax_show', $parameters),
            ['ownerId' => 0]
        );

        $result = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($result, Response::HTTP_OK);
    }

    public function testHide()
    {
        $parameters = [
            'menuName' => self::MENU_NAME,
            'key' => GlobalMenuUpdateData::MENU_UPDATE_1
        ];

        $this->ajaxRequest(
            'PUT',
            $this->getUrl('oro_commerce_menu_global_menu_ajax_hide', $parameters),
            ['ownerId' => 0]
        );

        $result = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($result, Response::HTTP_OK);
    }

    public function testMove()
    {
        $parameters = [
            'menuName' => self::MENU_NAME
        ];

        $this->ajaxRequest(
            'PUT',
            $this->getUrl('oro_commerce_menu_global_menu_ajax_move', $parameters),
            [
                'ownerId' => 0,
                'key' => GlobalMenuUpdateData::MENU_UPDATE_1_1,
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
            'menuName' => self::MENU_NAME
        ];

        $this->ajaxRequest(
            'DELETE',
            $this->getUrl('oro_commerce_menu_global_menu_ajax_reset', $parameters),
            ['ownerId' => 0]
        );

        $result = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($result, Response::HTTP_NO_CONTENT);
    }
}
