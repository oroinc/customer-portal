<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller;

use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomers;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\DomCrawler\Form;

class CustomerAddressControllerTest extends WebTestCase
{
    /** @var Customer */
    private $customer;

    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
        $this->loadFixtures([LoadCustomers::class]);

        $this->customer = $this->getReference('customer.orphan');
    }

    public function testCustomerView()
    {
        $this->client->request('GET', $this->getUrl('oro_customer_customer_view', ['id' => $this->customer->getId()]));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
    }

    /**
     * @depends testCustomerView
     */
    public function testCreateAddress(): int
    {
        $customer = $this->customer;
        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_customer_address_create',
                ['entityId' => $customer->getId(), '_widgetContainer' => 'dialog']
            )
        );

        $result = $this->client->getResponse();
        $this->assertEquals(200, $result->getStatusCode());

        $form = $crawler->selectButton('Save')->form();
        $this->fillFormForCreateTest($form);

        $this->client->followRedirects(true);
        $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $this->client->request(
            'GET',
            $this->getUrl('oro_api_customer_get_commercecustomer_address_primary', ['entityId' => $customer->getId()]),
            [],
            [],
            $this->generateWsseAuthHeader()
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertEquals('Badakhshān', $result['region']);
        $this->assertEquals([
            [
                'name' => AddressType::TYPE_BILLING,
                'label' => ucfirst(AddressType::TYPE_BILLING)
            ]
        ], $result['types']);

        $this->assertEquals([
            [
                'name' => AddressType::TYPE_BILLING,
                'label' => ucfirst(AddressType::TYPE_BILLING)
            ]
        ], $result['defaults']);

        return $customer->getId();
    }

    /**
     * @depends testCreateAddress
     */
    public function testUpdateAddress(int $id): int
    {
        $this->client->request(
            'GET',
            $this->getUrl('oro_api_customer_get_commercecustomer_address_primary', ['entityId' => $id]),
            [],
            [],
            $this->generateWsseAuthHeader()
        );

        $address = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_customer_address_update',
                ['entityId' => $id, 'id' => $address['id'], '_widgetContainer' => 'dialog']
            )
        );

        $result = $this->client->getResponse();
        $this->assertEquals(200, $result->getStatusCode());

        $form = $crawler->selectButton('Save')->form();
        $form = $this->fillFormForUpdateTest($form);

        $this->client->followRedirects(true);
        $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $this->client->request(
            'GET',
            $this->getUrl('oro_api_customer_get_commercecustomer_address_primary', ['entityId' => $id]),
            [],
            [],
            $this->generateWsseAuthHeader()
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertEquals('Manicaland', $result['region']);

        $this->assertCount(2, $result['types']);
        $this->assertContains(
            [
                'name' => AddressType::TYPE_SHIPPING,
                'label' => ucfirst(AddressType::TYPE_SHIPPING)
            ],
            $result['types']
        );
        $this->assertContains(
            [
                'name' => AddressType::TYPE_BILLING,
                'label' => ucfirst(AddressType::TYPE_BILLING)
            ],
            $result['types']
        );

        $this->assertEquals([
            [
                'name' => AddressType::TYPE_SHIPPING,
                'label' => ucfirst(AddressType::TYPE_SHIPPING)
            ]
        ], $result['defaults']);

        return $id;
    }

    /**
     * Fill form for address tests (create test)
     */
    private function fillFormForCreateTest(Form $form): Form
    {
        $formNode = $form->getNode();
        $formNode->setAttribute('action', $formNode->getAttribute('action') . '?_widgetContainer=dialog');

        $form['oro_customer_typed_address[street]'] = 'Street';
        $form['oro_customer_typed_address[city]'] = 'City';
        $form['oro_customer_typed_address[postalCode]'] = 'Zip code';
        $form['oro_customer_typed_address[organization]'] = 'Test Org';
        $form['oro_customer_typed_address[types]'] = [AddressType::TYPE_BILLING];
        $form['oro_customer_typed_address[defaults][default]'] = [AddressType::TYPE_BILLING];

        $doc = new \DOMDocument('1.0');
        $doc->loadHTML(
            '<select name="oro_customer_typed_address[country]" id="oro_customer_typed_address_country" ' .
            'tabindex="-1" class="select2-offscreen"> ' .
            '<option value="" selected="selected"></option> ' .
            '<option value="AF">Afghanistan</option> </select>'
        );
        $field = new ChoiceFormField($doc->getElementsByTagName('select')->item(0));
        $form->set($field);
        $form['oro_customer_typed_address[country]'] = 'AF';

        $doc->loadHTML(
            '<select name="oro_customer_typed_address[region]" id="oro_customer_typed_address_region" ' .
            'tabindex="-1" class="select2-offscreen"> ' .
            '<option value="" selected="selected"></option> ' .
            '<option value="AF-BDS">Badakhshān</option> </select>'
        );
        $field = new ChoiceFormField($doc->getElementsByTagName('select')->item(0));
        $form->set($field);
        $form['oro_customer_typed_address[region]'] = 'AF-BDS';

        return $form;
    }

    /**
     * Fill form for address tests (update test)
     */
    private function fillFormForUpdateTest(Form $form): Form
    {
        $formNode = $form->getNode();
        $formNode->setAttribute('action', $formNode->getAttribute('action') . '?_widgetContainer=dialog');

        $form['oro_customer_typed_address[types]'] = [AddressType::TYPE_BILLING, AddressType::TYPE_SHIPPING];
        $form['oro_customer_typed_address[defaults][default]'] = [false, AddressType::TYPE_SHIPPING];

        $doc = new \DOMDocument('1.0');
        $doc->loadHTML(
            '<select name="oro_customer_typed_address[country]" id="oro_customer_typed_address_country" ' .
            'tabindex="-1" class="select2-offscreen"> ' .
            '<option value="" selected="selected"></option> ' .
            '<option value="ZW">Zimbabwe</option> </select>'
        );
        $field = new ChoiceFormField($doc->getElementsByTagName('select')->item(0));
        $form->set($field);
        $form['oro_customer_typed_address[country]'] = 'ZW';

        $doc->loadHTML(
            '<select name="oro_customer_typed_address[region]" id="oro_customer_typed_address_region" ' .
            'tabindex="-1" class="select2-offscreen"> ' .
            '<option value="" selected="selected"></option> ' .
            '<option value="ZW-MA">Manicaland</option> </select>'
        );
        $field = new ChoiceFormField($doc->getElementsByTagName('select')->item(0));
        $form->set($field);
        $form['oro_customer_typed_address[region]'] = 'ZW-MA';

        return $form;
    }
}
