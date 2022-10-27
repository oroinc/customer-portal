<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Migrations\Data\ORM\LoadAnonymousCustomerGroup;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomers;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadGroups;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class CustomerGroupControllerTest extends WebTestCase
{
    private const NAME = 'Group_name';
    private const UPDATED_NAME = 'Group_name_UP';
    private const ADD_NOTE_BUTTON = 'Add note';

    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
        $this->loadFixtures([
            LoadCustomers::class,
            LoadGroups::class
        ]);
    }

    public function testIndex()
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_customer_customer_group_index'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        self::assertStringContainsString('customer-groups-grid', $crawler->html());
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
    public function testUpdate(): int
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
     */
    public function testView(int $id)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_customer_customer_group_view', ['id' => $id])
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $html = $crawler->html();
        self::assertStringContainsString(self::UPDATED_NAME . ' - Customer Groups - Customers', $html);
        self::assertStringContainsString(self::ADD_NOTE_BUTTON, $html);
        self::assertStringContainsString(self::UPDATED_NAME, $html);
    }

    private function assertCustomerGroupSave(
        Crawler $crawler,
        string $name,
        array $appendCustomers = [],
        array $removeCustomers = []
    ): void {
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
        $redirectAction = $crawler->selectButton('Save and Close')->attr('data-action');
        $form->setValues(['input_action' => $redirectAction]);

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $html = $crawler->html();

        self::assertStringContainsString('Customer group has been saved', $html);
        self::assertStringContainsString($name, $html);

        foreach ($appendCustomers as $customer) {
            self::assertStringContainsString($customer->getName(), $html);
        }
        foreach ($removeCustomers as $customer) {
            self::assertStringNotContainsString($customer->getName(), $html);
        }
    }

    private function getGroupId(string $name): int
    {
        /** @var CustomerGroup $customerGroup */
        $customerGroup = $this->getContainer()->get('doctrine')
            ->getRepository(CustomerGroup::class)
            ->findOneBy(['name' => $name]);

        return $customerGroup->getId();
    }
}
