<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Form\Type;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomers;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Test\Functional\RolePermissionExtension;
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
    use RolePermissionExtension;

    private const PRIMARY_ADDRESS = 0;

    protected function setUp(): void
    {
        $this->initClient([], self::generateBasicAuthHeader());
        $this->loadFixtures([LoadCustomers::class]);

        $this->updateRolePermissions(
            'ROLE_ADMINISTRATOR',
            CustomerAddress::class,
            [
                'CREATE' => AccessLevel::GLOBAL_LEVEL,
                'EDIT' => AccessLevel::GLOBAL_LEVEL,
                'VIEW' => AccessLevel::GLOBAL_LEVEL,
                'DELETE' => AccessLevel::GLOBAL_LEVEL,
            ]
        );
    }

    /**
     * @dataProvider formAddressTypeDataProvider
     */
    public function testCreatePrimaryAddress(array $addressData): void
    {
        $crawler = $this->submitCustomerForm($addressData);

        $addresses = $this->getCustomer()->getAddresses();

        self::assertCount(1, $addresses);
        self::assertTrue($addresses->first()->isPrimary());
        self::assertStringContainsString('Customer has been saved', $crawler->html());
        self::assertStringNotContainsString('One of the addresses must be set as primary.', $crawler->html());
    }

    public function formAddressTypeDataProvider(): array
    {
        $addressData = [
            'label' => 'Address',
            'firstName' => 'Acme',
            'lastName' => 'ACme',
            'country' => 'US',
            'street' => '19200 Canis Heights Drive',
            'city' => 'Los Angeles',
            'region' => 'US-CA',
            'postalCode' => '90071',
            'types' => ['billing', 'shipping'],
            'defaults' => ['default' => ['billing', 'shipping']],
        ];

        return [
            'set address as not primary' => [
                'address' => array_merge($addressData, ['primary' => false])
            ],
            'set address as primary' => [
                'address' => array_merge($addressData, ['primary' => true])
            ]
        ];
    }

    private function submitCustomerForm(array $addressData): Crawler
    {
        $form = $this->getUpdateForm();

        $submittedData = $form->getPhpValues();
        $submittedData['input_action'] = 'save_and_stay';
        $submittedData['oro_customer_type']['addresses'][self::PRIMARY_ADDRESS] = $addressData;

        $this->client->followRedirects(true);
        $crawler = $this->client->request($form->getMethod(), $form->getUri(), $submittedData);
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);

        return $crawler;
    }

    private function getUpdateForm(): Form
    {
        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->getUrl(
                'oro_customer_customer_update',
                ['id' => $this->getCustomer()->getId()]
            )
        );
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);

        return $crawler->selectButton('Save')->form();
    }

    private function getCustomer(): Customer
    {
        return $this->getReference(LoadCustomers::CUSTOMER_LEVEL_1);
    }
}
