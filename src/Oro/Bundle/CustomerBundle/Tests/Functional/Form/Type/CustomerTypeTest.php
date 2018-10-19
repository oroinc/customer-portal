<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Form\Type;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerAddresses;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomers;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Covers testing validation constraint for Customer Address
 */
class CustomerTypeTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient([], static::generateBasicAuthHeader());
        $this->loadFixtures([
            LoadCustomerAddresses::class
        ]);
    }

    /**
     * @param array $data
     * @dataProvider formAddressTypeDataProvider
     */
    public function testCreatePrimaryAddress(array $data): void
    {
        $crawler = $this->submitCustomerForm($data);

        $this->assertNotContains('One of the addresses must be set as primary.', $crawler->html());
        $this->assertContains('Customer has been saved', $crawler->html());
    }

    /**
     * @return array
     */
    public function formAddressTypeDataProvider(): array
    {
        return [
            'set address as not primary' => [
                'data' => [
                    'primary' => false
                ]
            ],
            'set address as primary' => [
                'data' => [
                    'primary' => true
                ]
            ]
        ];
    }

    /**
     * @param array $data
     * @return Crawler
     */
    private function submitCustomerForm(array $data): Crawler
    {
        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->getUrl(
                'oro_customer_customer_update',
                ['id' => $this->getCustomerId()]
            )
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);

        /** @var Form $form */
        $form = $crawler->selectButton('Save')->form();
        foreach ($data as $field => $value) {
            $form[sprintf('oro_customer_type[addresses][0][%s]', $field)] = $value;
        }

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);

        return $crawler;
    }

    /**
     * @return integer
     */
    private function getCustomerId(): int
    {
        /** @var Customer $customer */
        $customer = $this->getReference(LoadCustomers::CUSTOMER_LEVEL_1);

        return $customer->getId();
    }
}
