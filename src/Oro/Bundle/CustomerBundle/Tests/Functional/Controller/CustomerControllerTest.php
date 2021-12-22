<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomers;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadGroups;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadInternalRating;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadUserData;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class CustomerControllerTest extends WebTestCase
{
    private const ACCOUNT_NAME = 'Customer_name';
    private const UPDATED_NAME = 'Customer_name_UP';

    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
        $this->loadFixtures($this->getFixtureList());
    }

    public function testIndex()
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_customer_customer_index'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        self::assertStringContainsString('customer-customers-grid', $crawler->html());
        self::assertStringContainsString('Export', $result->getContent());
        self::assertStringNotContainsString('Created at', $result->getContent());
        self::assertStringNotContainsString('Updated at', $result->getContent());
    }

    public function testCreate(): int
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_customer_customer_create'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        /** @var Customer $parent */
        $parent = $this->getReference('customer.level_1');
        /** @var CustomerGroup $group */
        $group = $this->getReference('customer_group.group1');
        /** @var AbstractEnumValue $internalRating */
        $internalRating = $this->getReference('internal_rating.1 of 5');
        $this->assertCustomerSave($crawler, self::ACCOUNT_NAME, $parent, $group, $internalRating);

        /** @var Customer $customer */
        $customer = $this->getContainer()->get('doctrine')
            ->getRepository(Customer::class)
            ->findOneBy(['name' => self::ACCOUNT_NAME]);
        $this->assertNotEmpty($customer);
        $this->assertNotEmpty($customer->getCreatedAt());
        $this->assertEquals($customer->getCreatedAt(), $customer->getUpdatedAt());

        return $customer->getId();
    }

    /**
     * @depends testCreate
     */
    public function testUpdate(int $id): int
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_customer_customer_update', ['id' => $id])
        );
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        /** @var Customer $newParent */
        $newParent = $this->getReference('customer.level_1.1');
        /** @var CustomerGroup $newGroup */
        $newGroup = $this->getReference('customer_group.group2');
        /** @var AbstractEnumValue $internalRating */
        $internalRating = $this->getReference('internal_rating.2 of 5');
        $this->assertCustomerSave($crawler, self::UPDATED_NAME, $newParent, $newGroup, $internalRating);

        return $id;
    }

    /**
     * @depends testUpdate
     */
    public function testView(int $id)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_customer_customer_view', ['id' => $id])
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $html = $crawler->html();
        self::assertStringContainsString(self::UPDATED_NAME . ' - Customers - Customers', $html);
        self::assertStringContainsString('Add attachment', $html);
        self::assertStringContainsString('Add note', $html);
        self::assertStringContainsString('Address Book', $html);
        /** @var Customer $newParent */
        $newParent = $this->getReference('customer.level_1.1');
        /** @var CustomerGroup $newGroup */
        $newGroup = $this->getReference('customer_group.group2');
        /** @var AbstractEnumValue $internalRating */
        $internalRating = $this->getReference('internal_rating.2 of 5');
        $this->assertViewPage($html, self::UPDATED_NAME, $newParent, $newGroup, $internalRating);
    }

    private function assertCustomerSave(
        Crawler $crawler,
        string $name,
        Customer $parent,
        CustomerGroup $group,
        AbstractEnumValue $internalRating
    ): void {
        $form = $crawler->selectButton('Save and Close')->form(
            $this->prepareFormValues($name, $parent, $group, $internalRating)
        );

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $html = $crawler->html();

        self::assertStringContainsString('Customer has been saved', $html);
        $this->assertViewPage($html, $name, $parent, $group, $internalRating);
        self::assertStringContainsString(
            $this->getReference(LoadUserData::USER1)->getFullName(),
            $result->getContent()
        );
        self::assertStringContainsString(
            $this->getReference(LoadUserData::USER2)->getFullName(),
            $result->getContent()
        );
    }

    private function assertViewPage(
        string $html,
        string $name,
        Customer $parent,
        CustomerGroup $group,
        AbstractEnumValue $internalRating
    ): void {
        self::assertStringContainsString($name, $html);
        self::assertStringContainsString($parent->getName(), $html);
        self::assertStringContainsString($group->getName(), $html);
        self::assertStringContainsString($internalRating->getName(), $html);
    }

    protected function prepareFormValues(
        string $name,
        Customer $parent,
        CustomerGroup $group,
        AbstractEnumValue $internalRating
    ): array {
        return [
            'oro_customer_type[name]' => $name,
            'oro_customer_type[parent]' => $parent->getId(),
            'oro_customer_type[group]' => $group->getId(),
            'oro_customer_type[internal_rating]' => $internalRating->getId(),
            'oro_customer_type[salesRepresentatives]' => implode(',', [
                $this->getReference(LoadUserData::USER1)->getId(),
                $this->getReference(LoadUserData::USER2)->getId()
            ])
        ];
    }

    protected function getFixtureList(): array
    {
        return [
            LoadCustomers::class,
            LoadGroups::class,
            LoadInternalRating::class,
            LoadUserData::class
        ];
    }
}
