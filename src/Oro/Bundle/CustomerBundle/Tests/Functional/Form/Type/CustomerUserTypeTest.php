<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Form\Type;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserAddresses;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Test\Functional\RolePermissionExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Covers testing validation constraint for Customer User Address
 */
class CustomerUserTypeTest extends WebTestCase
{
    use RolePermissionExtension;

    protected function setUp()
    {
        $this->initClient([], static::generateBasicAuthHeader());
        $this->loadFixtures([
            LoadCustomerUserAddresses::class
        ]);

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
     * @param array $data
     * @dataProvider formWrongDataProvider
     */
    public function testUpdateAddressWithWrongData(array $data): void
    {
        $crawler = $this->submitCustomerUserForm($data);

        $this->assertContains('First Name and Last Name or Organization should not be blank.', $crawler->html());
        $this->assertContains('Last Name and First Name or Organization should not be blank.', $crawler->html());
        $this->assertContains('Organization or First Name and Last Name should not be blank.', $crawler->html());
        $this->assertNotContains('Customer User has been saved', $crawler->html());
    }

    /**
     * @return array
     */
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
     * @param array $data
     * @dataProvider formCorrectDataProvider
     */
    public function testUpdateAddressWithCorrectData(array $data): void
    {
        $crawler = $this->submitCustomerUserForm($data);

        $this->assertNotContains('First Name and Last Name or Organization should not be blank.', $crawler->html());
        $this->assertNotContains('Last Name and First Name or Organization should not be blank.', $crawler->html());
        $this->assertNotContains('Organization or First Name and Last Name should not be blank.', $crawler->html());
        $this->assertContains('Customer User has been saved', $crawler->html());
    }

    /**
     * @return array
     */
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
     * @param array $data
     * @dataProvider formAddressTypeDataProvider
     */
    public function testCreatePrimaryAddress(array $data): void
    {
        $crawler = $this->submitCustomerUserForm($data);

        $this->assertNotContains('One of the addresses must be set as primary.', $crawler->html());
        $this->assertContains('Customer User has been saved', $crawler->html());
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

        /** @var Form $form */
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

    /**
     * @return integer
     */
    private function getCustomerUserId(): int
    {
        /** @var CustomerUser $customerUser */
        $customerUser = $this->getReference(LoadCustomerUserData::EMAIL);

        return $customerUser->getId();
    }
}
