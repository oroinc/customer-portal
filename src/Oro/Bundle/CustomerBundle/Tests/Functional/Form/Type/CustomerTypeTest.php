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
        $this->loadFixtures([LoadCustomerAddresses::class]);
    }

    /**
     * @param bool $primary
     *
     * @dataProvider formAddressTypeDataProvider
     */
    public function testCreatePrimaryAddress(bool $primary): void
    {
        $this->markTestSkipped('BAP-17722');
        $crawler = $this->submitCustomerForm($primary);

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
                'primary' => false
            ],
            'set address as primary' => [
                'primary' => true
            ]
        ];
    }

    /**
     * @param bool $primary
     *
     * @return Crawler
     */
    private function submitCustomerForm(bool $primary): Crawler
    {
        $form = $this->getUpdateForm();

        $submittedData = $form->getPhpValues();
        $submittedData['input_action'] = 'save_and_stay';
        $submittedData['oro_customer_type']['addresses'][0]['primary'] = $primary;

        $this->client->followRedirects(true);
        $crawler = $this->client->request($form->getMethod(), $form->getUri(), $submittedData);
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);

        return $crawler;
    }

    /**
     * @return Form
     */
    private function getUpdateForm()
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

        return $crawler->selectButton('Save')->form();
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
