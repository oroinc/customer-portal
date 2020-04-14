<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Functional\Controller;

use Oro\Bundle\CommerceMenuBundle\Tests\Functional\DataFixtures\CustomerMenuUpdateData;
use Oro\Bundle\CommerceMenuBundle\Tests\Functional\DataFixtures\GlobalMenuUpdateData;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomers;
use Oro\Bundle\NavigationBundle\Entity\MenuUpdate;
use Oro\Bundle\NavigationBundle\Entity\Repository\MenuUpdateRepository;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\WebsiteBundle\Tests\Functional\DataFixtures\LoadWebsiteData;

class CustomerMenuControllerTest extends WebTestCase
{
    const MENU_NAME = 'frontend_menu';

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());

        $this->loadFixtures([GlobalMenuUpdateData::class, CustomerMenuUpdateData::class]);
    }

    public function testIndex()
    {
        $this->client->request(
            'GET',
            $this->getUrl('oro_commerce_menu_customer_menu_index', ['id' => $this->getCustomerId()])
        );
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
    }

    public function testView()
    {
        $url = $this->getUrl(
            'oro_commerce_menu_customer_menu_view',
            [
                'menuName' => self::MENU_NAME,
                'context' => [
                    'customer' => $this->getCustomerId(),
                    'website' => $this->getWebsiteId()
                ]
            ]
        );
        $crawler = $this->client->request('GET', $url);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        static::assertStringContainsString(
            'Select existing menu item or create new.',
            $crawler->filter('[data-role="content"] .tree-empty-content .no-data')->html()
        );
    }

    public function testCreate()
    {
        $url = $this->getUrl(
            'oro_commerce_menu_customer_menu_create',
            [
                'menuName' => self::MENU_NAME,
                'context' => [
                    'customer' => $this->getCustomerId(),
                    'website' => $this->getWebsiteId()
                ]
            ]
        );
        $crawler = $this->client->request('GET', $url);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $form = $crawler->selectButton('Save')->form();
        $form['menu_update[titles][values][default]'] = 'menu_update.new.title.default';
        $form['menu_update[descriptions][values][default]'] = 'menu_update.new.description.default';
        $form['menu_update[targetType]'] = 'uri';
        $form['menu_update[uri]'] = '#menu_update.new';

        $this->client->followRedirects(true);

        $crawler = $this->client->submit($form);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        static::assertStringContainsString('Menu item saved successfully.', $crawler->html());
    }

    public function testCreateChild()
    {
        $url = $this->getUrl(
            'oro_commerce_menu_customer_menu_create',
            [
                'menuName' => self::MENU_NAME,
                'parentKey' => GlobalMenuUpdateData::MENU_UPDATE_1,
                'context' => [
                    'customer' => $this->getCustomerId(),
                    'website' => $this->getWebsiteId()
                ]
            ]
        );
        $crawler = $this->client->request('GET', $url);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $form = $crawler->selectButton('Save')->form();
        $form['menu_update[titles][values][default]'] = 'menu_update.child.title.default';
        $form['menu_update[descriptions][values][default]'] = 'menu_update.child.description.default';
        $form['menu_update[targetType]'] = 'uri';
        $form['menu_update[uri]'] = '#menu_update.child';

        $this->client->followRedirects(true);

        $crawler = $this->client->submit($form);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        static::assertStringContainsString('Menu item saved successfully.', $crawler->html());
    }

    public function testUpdateCustom()
    {
        /** @var MenuUpdate $reference */
        $reference = $this->getReference(CustomerMenuUpdateData::MENU_UPDATE_1);

        $url = $this->getUrl(
            'oro_commerce_menu_customer_menu_update',
            [
                'menuName' => self::MENU_NAME,
                'key' => $reference->getKey(),
                'context' => [
                    'customer' => $this->getCustomerId(),
                    'website' => $this->getWebsiteId()
                ]
            ]
        );
        $crawler = $this->client->request('GET', $url);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $form = $crawler->selectButton('Save')->form();
        $form['menu_update[titles][values][default]'] = 'menu_update.changed.title.default';
        $form['menu_update[descriptions][values][default]'] = 'menu_update.changed.description.default';
        $form['menu_update[targetType]'] = 'uri';
        $form['menu_update[uri]'] = '#menu_update.changed';

        $this->client->followRedirects(true);

        $crawler = $this->client->submit($form);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $html = $crawler->html();
        static::assertStringContainsString('Menu item saved successfully.', $html);
        static::assertStringContainsString('menu_update.changed.title.default', $html);
    }

    public function testUpdateNotCustom()
    {
        $url = $this->getUrl(
            'oro_commerce_menu_customer_menu_update',
            [
                'menuName' => self::MENU_NAME,
                'key' => GlobalMenuUpdateData::MENU_UPDATE_1,
                'context' => [
                    'customer' => $this->getCustomerId(),
                    'website' => $this->getWebsiteId()
                ]
            ]
        );
        $crawler = $this->client->request('GET', $url);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        static::assertStringContainsString(
            $this->getContainer()->get('translator')->trans('oro.navigation.menu.menu_list_default.label'),
            $crawler->html()
        );

        $form = $crawler->selectButton('Save')->form();

        $this->client->followRedirects(true);

        $crawler = $this->client->submit($form);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $html = $crawler->html();
        static::assertStringContainsString('Menu item saved successfully.', $html);
        static::assertStringContainsString('menu_update.changed.title.default', $html);
    }

    public function testMove()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_commerce_menu_customer_menu_move',
                [
                    'menuName' => self::MENU_NAME,
                    'context' => [
                        'customer' => $this->getCustomerId(),
                        'website' => $this->getWebsiteId()
                    ]
                ]
            ),
            [
                'selected' => [
                    $this->getReference(CustomerMenuUpdateData::MENU_UPDATE_1_1)->getKey()
                ],
                '_widgetContainer' => 'dialog',
            ],
            [],
            $this->generateWsseAuthHeader()
        );

        $form = $crawler->selectButton('Save')->form();
        $form['tree_move[target]'] = self::MENU_NAME;

        $this->client->followRedirects(true);

        $form->getFormNode()->setAttribute(
            'action',
            $form->getFormNode()->getAttribute('action')
        );

        $this->client->submit($form);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        /** @var MenuUpdateRepository $repository */
        $repository = $this->getContainer()->get('doctrine')
            ->getManagerForClass('OroCommerceMenuBundle:MenuUpdate')
            ->getRepository('OroCommerceMenuBundle:MenuUpdate');
        $menuUpdate = $repository->findOneBy(['key' => CustomerMenuUpdateData::MENU_UPDATE_1_1]);
        $this->assertNull($menuUpdate->getParentKey());
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
