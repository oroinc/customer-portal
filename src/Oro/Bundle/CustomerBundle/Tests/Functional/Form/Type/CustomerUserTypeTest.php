<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Form\Type;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserAddresses;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Test\Functional\RolePermissionExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Covers testing validation constraint for Customer User Address
 */
class CustomerUserTypeTest extends WebTestCase
{
    use RolePermissionExtension;

    protected function setUp(): void
    {
        $this->initClient([], self::generateBasicAuthHeader());
        $this->loadFixtures([LoadCustomerUserAddresses::class]);

        $this->updateRolePermissions(
            'ROLE_ADMINISTRATOR',
            CustomerUserAddress::class,
            [
                'CREATE' => AccessLevel::GLOBAL_LEVEL,
                'EDIT' => AccessLevel::GLOBAL_LEVEL,
                'VIEW' => AccessLevel::GLOBAL_LEVEL,
                'DELETE' => AccessLevel::GLOBAL_LEVEL,
            ]
        );
    }

    /**
     * @dataProvider formWrongDataProvider
     */
    public function testUpdateAddressWithWrongData(array $data): void
    {
        $crawler = $this->submitCustomerUserForm($data);

        self::assertStringContainsString(
            'First Name and Last Name or Organization should not be blank.',
            $crawler->html()
        );
        self::assertStringContainsString(
            'Last Name and First Name or Organization should not be blank.',
            $crawler->html()
        );
        self::assertStringContainsString(
            'Organization or First Name and Last Name should not be blank.',
            $crawler->html()
        );
        self::assertStringNotContainsString(
            'Customer User has been saved',
            $crawler->html()
        );
    }

    public function formWrongDataProvider(): array
    {
        return [
            'empty FirstName' => [
                'data' => [
                    'firstName' => '',
                    'lastName' => 'Cole',
                    'organization' => ''
                ]
            ],
            'empty LastName' => [
                'data' => [
                    'firstName' => 'Amanda',
                    'lastName' => '',
                    'organization' => ''
                ]
            ],
            'empty First and LastName' => [
                'data' => [
                    'firstName' => '',
                    'lastName' => '',
                    'organization' => ''
                ]
            ]
        ];
    }

    /**
     * @dataProvider formCorrectDataProvider
     */
    public function testUpdateAddressWithCorrectData(array $data): void
    {
        $crawler = $this->submitCustomerUserForm($data);

        self::assertStringNotContainsString(
            'First Name and Last Name or Organization should not be blank.',
            $crawler->html()
        );
        self::assertStringNotContainsString(
            'Last Name and First Name or Organization should not be blank.',
            $crawler->html()
        );
        self::assertStringNotContainsString(
            'Organization or First Name and Last Name should not be blank.',
            $crawler->html()
        );
        self::assertStringContainsString('Customer User has been saved', $crawler->html());
    }

    public function formCorrectDataProvider(): array
    {
        return [
            'empty Organization' => [
                'data' => [
                    'firstName' => 'Amanda',
                    'lastName' => 'Cole',
                    'organization' => ''
                ]
            ],
            'empty First And LastName' => [
                'data' => [
                    'firstName' => '',
                    'lastName' => '',
                    'organization' => 'organization'
                ]
            ],
            '0 as Organization' => [
                'data' => [
                    'firstName' => '',
                    'lastName' => '',
                    'organization' => '0'
                ]
            ],
            '0 as FirstName And LastName' => [
                'data' => [
                    'firstName' => '0',
                    'lastName' => '0',
                    'organization' => ''
                ]
            ],
            'set all fields' => [
                'data' => [
                    'firstName' => 'Amanda',
                    'lastName' => 'Cole',
                    'organization' => 'organization'
                ]
            ]
        ];
    }

    /**
     * @dataProvider formAddressTypeDataProvider
     */
    public function testCreatePrimaryAddress(array $data): void
    {
        $crawler = $this->submitCustomerUserForm($data);

        self::assertStringNotContainsString('One of the addresses must be set as primary.', $crawler->html());
        self::assertStringContainsString('Customer User has been saved', $crawler->html());
    }

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

    private function submitCustomerUserForm(array $data): Crawler
    {
        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->getUrl(
                'oro_customer_customer_user_update',
                ['id' => $this->getCustomerUserId()]
            )
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);

        $form = $crawler->selectButton('Save')->form();
        $formValues = $form->getPhpValues();
        foreach ($data as $field => $value) {
            $formValues['oro_customer_customer_user']['addresses'][0][$field] = $value;
        }

        $this->client->followRedirects(true);
        $crawler = $this->client->request($form->getMethod(), $form->getUri(), $formValues);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);

        return $crawler;
    }

    private function getCustomerUserId(): int
    {
        return $this->getReference(LoadCustomerUserData::EMAIL)->getId();
    }
}
