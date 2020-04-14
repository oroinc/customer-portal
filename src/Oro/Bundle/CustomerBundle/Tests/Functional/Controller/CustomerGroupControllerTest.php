<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Migrations\Data\ORM\LoadAnonymousCustomerGroup;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class CustomerGroupControllerTest extends WebTestCase
{
    const NAME = 'Group_name';
    const UPDATED_NAME = 'Group_name_UP';
    const ADD_NOTE_BUTTON = 'Add note';

    /**
     * @var EntityManager
     */
    protected $entityManager;

    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
        $this->entityManager = $this->getContainer()
            ->get('doctrine')
            ->getManagerForClass('OroCustomerBundle:CustomerGroup');

        $this->loadFixtures(
            [
                'Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomers',
                'Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadGroups'
            ]
        );
    }

    public function testIndex()
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_customer_customer_group_index'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertStringContainsString('customer-groups-grid', $crawler->html());
    }

    public function testGrid()
    {
        $response = $this->client->requestGrid(
            'customer-groups-grid',
            ['customer-groups-grid[_filter][name][value]' => LoadAnonymousCustomerGroup::GROUP_NAME_NON_AUTHENTICATED]
        );

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);

        $this->assertEquals(LoadAnonymousCustomerGroup::GROUP_NAME_NON_AUTHENTICATED, $result['name']);
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_customer_customer_group_create'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $this->assertCustomerGroupSave(
            $crawler,
            self::NAME,
            [
                $this->getReference('customer.level_1.1'),
                $this->getReference('customer.level_1.2')
            ]
        );
    }

    /**
     * @depends testCreate
     */
    public function testUpdate()
    {
        $id = $this->getGroupId(self::NAME);
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_customer_customer_group_update', ['id' => $id])
        );
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertCustomerGroupSave(
            $crawler,
            self::UPDATED_NAME,
            [
                $this->getReference('customer.level_1.1.1')
            ],
            [
                $this->getReference('customer.level_1.2')
            ]
        );

        return $id;
    }

    /**
     * @depends testUpdate
     * @param int $id
     */
    public function testView($id)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_customer_customer_group_view', ['id' => $id])
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $html = $crawler->html();
        static::assertStringContainsString(self::UPDATED_NAME . ' - Customer Groups - Customers', $html);
        static::assertStringContainsString(self::ADD_NOTE_BUTTON, $html);
        $this->assertViewPage($html, self::UPDATED_NAME);
    }

    /**
     * @param Crawler $crawler
     * @param string $name
     * @param Customer[] $appendCustomers
     * @param Customer[] $removeCustomers
     */
    protected function assertCustomerGroupSave(
        Crawler $crawler,
        $name,
        array $appendCustomers = [],
        array $removeCustomers = []
    ) {
        $appendCustomerIds = array_map(
            function (Customer $customer) {
                return $customer->getId();
            },
            $appendCustomers
        );
        $removeCustomerIds = array_map(
            function (Customer $customer) {
                return $customer->getId();
            },
            $removeCustomers
        );
        $form = $crawler->selectButton('Save and Close')->form(
            [
                'oro_customer_group_type[name]' => $name,
                'oro_customer_group_type[appendCustomers]' => implode(',', $appendCustomerIds),
                'oro_customer_group_type[removeCustomers]' => implode(',', $removeCustomerIds)
            ]
        );

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $html = $crawler->html();

        static::assertStringContainsString('Customer group has been saved', $html);
        $this->assertViewPage($html, $name);

        foreach ($appendCustomers as $customer) {
            static::assertStringContainsString($customer->getName(), $html);
        }
        foreach ($removeCustomers as $customer) {
            static::assertStringNotContainsString($customer->getName(), $html);
        }
    }

    /**
     * @param string $html
     * @param string $name
     */
    protected function assertViewPage($html, $name)
    {
        static::assertStringContainsString($name, $html);
    }

    /**
     * @param string $name
     * @return int
     */
    protected function getGroupId($name)
    {
        /** @var CustomerGroup $customerGroup */
        $customerGroup = $this->getContainer()->get('doctrine')
            ->getManagerForClass('OroCustomerBundle:CustomerGroup')
            ->getRepository('OroCustomerBundle:CustomerGroup')
            ->findOneBy(['name' => $name]);

        return $customerGroup->getId();
    }
}
