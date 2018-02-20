<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Functional\Controller;

use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\CommerceMenuBundle\Tests\Functional\DataFixtures\GlobalMenuUpdateData;
use Oro\Bundle\NavigationBundle\Entity\Repository\MenuUpdateRepository;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class GlobalMenuControllerTest extends WebTestCase
{
    const FRONTEND_MENU_NAME = 'frontend_menu';
    const FEATURED_MENU_NAME = 'featured_menu';

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());

        $this->loadFixtures([GlobalMenuUpdateData::class]);
    }

    public function testIndex()
    {
        $this->client->request('GET', $this->getUrl('oro_commerce_menu_global_menu_index'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
    }

    public function testView()
    {
        $url = $this->getUrl('oro_commerce_menu_global_menu_view', ['menuName' => self::FRONTEND_MENU_NAME]);
        $crawler = $this->client->request('GET', $url);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $this->assertContains(
            'Select existing menu item or create new.',
            $crawler->filter('[data-role="content"] .tree-empty-content')->html()
        );
    }

    public function testCreate()
    {
        $url = $this->getUrl('oro_commerce_menu_global_menu_create', ['menuName' => self::FRONTEND_MENU_NAME]);
        $crawler = $this->client->request('GET', $url);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $form = $crawler->selectButton('Save')->form();

        $form['menu_update[titles][values][default]'] = 'menu_update.new.title.default';
        $form['menu_update[descriptions][values][default]'] = 'menu_update.new.description.default';
        $form['menu_update[uri]'] = '#menu_update.new';

        $this->client->followRedirects(true);

        $crawler = $this->client->submit($form);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $this->assertContains('Menu item saved successfully.', $crawler->html());
    }

    public function testCreateChild()
    {
        $url = $this->getUrl(
            'oro_commerce_menu_global_menu_create',
            [
                'menuName' => self::FRONTEND_MENU_NAME,
                'parentKey' => GlobalMenuUpdateData::MENU_UPDATE_1
            ]
        );
        $crawler = $this->client->request('GET', $url);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $form = $crawler->selectButton('Save')->form();
        $form['menu_update[titles][values][default]'] = 'menu_update.child.title.default';
        $form['menu_update[descriptions][values][default]'] = 'menu_update.child.description.default';
        $form['menu_update[uri]'] = '#menu_update.child';
        $form['menu_update[condition]'] = 'true';

        $this->client->followRedirects(true);

        $crawler = $this->client->submit($form);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $this->assertContains('Menu item saved successfully.', $crawler->html());
    }

    public function testCreateInNotExistingMenu()
    {
        $url = $this->getUrl('oro_commerce_menu_global_menu_create', ['menuName' => 'not_existing_menu']);
        $crawler = $this->client->request('GET', $url);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 404);
    }

    public function testCreateInMenuWithoutChildren()
    {
        $url = $this->getUrl('oro_commerce_menu_global_menu_create', ['menuName' => self::FEATURED_MENU_NAME]);
        $crawler = $this->client->request('GET', $url);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $form = $crawler->selectButton('Save')->form();

        $form['menu_update[titles][values][default]'] = 'menu_update.new.title.default';
        $form['menu_update[descriptions][values][default]'] = 'menu_update.new.description.default';
        $form['menu_update[uri]'] = '#menu_update.new';

        $this->client->followRedirects(true);

        $crawler = $this->client->submit($form);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $this->assertContains('Menu item saved successfully.', $crawler->html());
    }

    public function testUpdateCustom()
    {
        /** @var MenuUpdate $reference */
        $reference = $this->getReference(GlobalMenuUpdateData::MENU_UPDATE_1);

        $url = $this->getUrl(
            'oro_commerce_menu_global_menu_update',
            [
                'menuName' => self::FRONTEND_MENU_NAME,
                'key' => $reference->getKey()
            ]
        );
        $crawler = $this->client->request('GET', $url);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $form = $crawler->selectButton('Save')->form();
        $form['menu_update[titles][values][default]'] = 'menu_update.changed.title.default';
        $form['menu_update[descriptions][values][default]'] = 'menu_update.changed.description.default';
        $form['menu_update[uri]'] = '#menu_update.changed';

        $this->client->followRedirects(true);

        $crawler = $this->client->submit($form);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $html = $crawler->html();
        $this->assertContains('Menu item saved successfully.', $html);
        $this->assertContains('menu_update.changed.title.default', $html);
    }

    public function testUpdateNotCustom()
    {
        $url = $this->getUrl(
            'oro_commerce_menu_global_menu_update',
            [
                'menuName' => self::FRONTEND_MENU_NAME,
                'key' => 'oro_customer_menu_customer_user_index'
            ]
        );
        $crawler = $this->client->request('GET', $url);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $this->assertContains(
            $this->getContainer()->get('translator')->trans('oro.navigation.menu.menu_list_default.label'),
            $crawler->html()
        );

        $form = $crawler->selectButton('Save')->form();

        $this->client->followRedirects(true);

        $crawler = $this->client->submit($form);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $html = $crawler->html();
        $this->assertContains('Menu item saved successfully.', $html);
        $this->assertContains('menu_update.changed.title.default', $html);
    }

    public function testMove()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_commerce_menu_global_menu_move',
                ['menuName' => self::FRONTEND_MENU_NAME]
            ),
            [
                'selected' => [
                    $this->getReference(GlobalMenuUpdateData::MENU_UPDATE_1_1)->getKey()
                ],
                '_widgetContainer' => 'dialog',
            ],
            [],
            $this->generateWsseAuthHeader()
        );
        $form = $crawler->selectButton('Save')->form();
        $form['tree_move[target]'] = self::FRONTEND_MENU_NAME;

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
        $menuUpdate = $repository->findOneBy(['key' => GlobalMenuUpdateData::MENU_UPDATE_1_1]);
        $this->assertNull($menuUpdate->getParentKey());
    }
}
