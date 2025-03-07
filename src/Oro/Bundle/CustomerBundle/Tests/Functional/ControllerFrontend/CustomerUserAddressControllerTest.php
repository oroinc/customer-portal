<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ControllerFrontend;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserAddressACLData;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserAddressesACLData;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\DomCrawler\Form;

class CustomerUserAddressControllerTest extends WebTestCase
{
    #[\Override]
    protected function setUp(): void
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader(
                LoadCustomerUserData::EMAIL,
                LoadCustomerUserData::PASSWORD
            )
        );

        $this->loadFixtures([LoadCustomerUserAddressesACLData::class, LoadCustomerUserData::class]);
    }

    public function testIndex()
    {
        $this->markTestSkipped('Should be fixed after BAP-10981');
        $this->loginUser(LoadCustomerUserAddressACLData::USER_ACCOUNT_2_ROLE_LOCAL);
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_customer_frontend_customer_user_address_index')
        );

        $addCompanyAddressLink = $crawler->selectLink('New Company Address')->link();
        $addUserAddressLink = $crawler->selectLink('New Address')->link();
        $this->assertNotEmpty($addCompanyAddressLink);
        $this->assertNotEmpty($addUserAddressLink);
        $addressLists = $crawler->filter('.address-list');
        $this->assertCount(2, $addressLists);
    }

    public function testCreatePageHasSameSiteCancelUrl(): void
    {
        $user = $this->getReference(LoadCustomerUserAddressACLData::USER_ACCOUNT_2_ROLE_LOCAL);
        $referer = 'http://example.org';
        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_customer_frontend_customer_user_address_create',
                ['entityId' => $user->getId()]
            ),
            [],
            [],
            ['HTTP_REFERER' => $referer]
        );

        self::assertHtmlResponseStatusCodeEquals($this->client->getResponse(), 200);

        $backToUrl = $crawler->selectLink('Cancel')->attr('href');
        self::assertNotEquals($referer, $backToUrl);
    }

    public function testCreate()
    {
        $user = $this->getReference(LoadCustomerUserAddressACLData::USER_ACCOUNT_2_ROLE_LOCAL);
        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_customer_frontend_customer_user_address_create',
                ['entityId' => $user->getId()]
            )
        );

        $this->assertHtmlResponseStatusCodeEquals($this->client->getResponse(), 200);

        $form = $crawler->selectButton('Save')->form();

        $this->fillFormForCreate($form);

        $this->client->followRedirects(true);

        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        self::assertStringContainsString('Customer User Address has been saved', $crawler->html());
    }

    public function testCreateAccessForbidden()
    {
        $this->loginUser(LoadCustomerUserAddressACLData::USER_ACCOUNT_2_ROLE_LOCAL);
        $user = $this->getReference(LoadCustomerUserAddressACLData::USER_ACCOUNT_1_2_ROLE_BASIC);
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_customer_frontend_customer_user_address_create',
                ['entityId' => $user->getId()]
            )
        );

        $this->assertHtmlResponseStatusCodeEquals($this->client->getResponse(), 403);
    }

    private function fillFormForCreate(Form $form): Form
    {
        $form['oro_customer_frontend_customer_user_typed_address[label]'] = 'Address Label';
        $form['oro_customer_frontend_customer_user_typed_address[primary]'] = true;
        $form['oro_customer_frontend_customer_user_typed_address[namePrefix]'] = 'pref';
        $form['oro_customer_frontend_customer_user_typed_address[firstName]'] = 'first';
        $form['oro_customer_frontend_customer_user_typed_address[middleName]'] = 'middle';
        $form['oro_customer_frontend_customer_user_typed_address[lastName]'] = 'last';
        $form['oro_customer_frontend_customer_user_typed_address[nameSuffix]'] = 'suffix';
        $form['oro_customer_frontend_customer_user_typed_address[organization]'] = 'org';
        $form['oro_customer_frontend_customer_user_typed_address[phone]'] = '+05000000';
        $form['oro_customer_frontend_customer_user_typed_address[street]'] = 'Street, 1';
        $form['oro_customer_frontend_customer_user_typed_address[street2]'] = 'Street, 2';
        $form['oro_customer_frontend_customer_user_typed_address[city]'] = 'London';

        $form['oro_customer_frontend_customer_user_typed_address[postalCode]'] = 10500;

        $form['oro_customer_frontend_customer_user_typed_address[types]'] = [
            AddressType::TYPE_BILLING,
            AddressType::TYPE_SHIPPING
        ];
        $form['oro_customer_frontend_customer_user_typed_address[defaults][default]'] = [
            false,
            AddressType::TYPE_SHIPPING
        ];

        $doc = new \DOMDocument('1.0');
        $doc->loadHTML(
            '<select name="oro_customer_frontend_customer_user_typed_address[country]" ' .
            'id="oro_customer_frontend_customer_user_typed_address_country" ' .
            'tabindex="-1" class="select2-offscreen"> ' .
            '<option value="" selected="selected"></option> ' .
            '<option value="ZW">Zimbabwe</option> </select>'
        );
        $field = new ChoiceFormField($doc->getElementsByTagName('select')->item(0));
        $form->set($field);
        $form['oro_customer_frontend_customer_user_typed_address[country]'] = 'ZW';

        $selectedOwner = $this->getReference(LoadCustomerUserAddressACLData::USER_ACCOUNT_2_ROLE_BASIC);
        $doc->loadHTML(
            '<select name="oro_customer_frontend_customer_user_typed_address[frontendOwner]" ' .
            'id="oro_customer_frontend_customer_user_typed_address_frontend_owner" ' .
            'tabindex="-1" class="select2-offscreen"> ' .
            '<option value="" selected="selected"></option> ' .
            '<option value="' . $selectedOwner->getId() . '">CustomerUser</option> </select>'
        );
        $field = new ChoiceFormField($doc->getElementsByTagName('select')->item(0));
        $form->set($field);

        $form['oro_customer_frontend_customer_user_typed_address[frontendOwner]'] = $selectedOwner->getId();

        $doc->loadHTML(
            '<select name="oro_customer_frontend_customer_user_typed_address[region]" ' .
            'id="oro_customer_frontend_customer_user_typed_address_country_region" ' .
            'tabindex="-1" class="select2-offscreen"> ' .
            '<option value="" selected="selected"></option> ' .
            '<option value="ZW-MA">Manicaland</option> </select>'
        );
        $field = new ChoiceFormField($doc->getElementsByTagName('select')->item(0));
        $form->set($field);
        $form['oro_customer_frontend_customer_user_typed_address[region]'] = 'ZW-MA';

        return $form;
    }

    /**
     * @depends testCreate
     */
    public function testUpdate()
    {
        $this->loginUser(LoadCustomerUserAddressACLData::USER_ACCOUNT_2_ROLE_LOCAL);
        $user = $this->getReference(LoadCustomerUserAddressACLData::USER_ACCOUNT_2_ROLE_LOCAL);
        /** @var CustomerUserAddress $address */
        $address = $user->getAddresses()->first();

        $this->assertInstanceOf(CustomerUserAddress::class, $address);

        $addressId = $address->getId();

        unset($address);

        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_customer_frontend_customer_user_address_update',
                ['entityId' => $user->getId(), 'id' => $addressId]
            )
        );

        $this->assertHtmlResponseStatusCodeEquals($this->client->getResponse(), 200);

        $form = $crawler->selectButton('Save')->form();

        $form['oro_customer_frontend_customer_user_typed_address[label]'] = 'Changed Label';

        $this->client->followRedirects(true);

        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        self::assertStringContainsString('Customer User Address has been saved', $crawler->html());

        $address = $this->getUserAddressById($addressId);

        $this->assertInstanceOf(CustomerUserAddress::class, $address);

        $this->assertEquals('Changed Label', $address->getLabel());
    }

    private function getUserAddressById(int $addressId): CustomerUserAddress
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get('doctrine')->getManager();
        $em->clear(CustomerUserAddress::class);

        return $em->getRepository(CustomerUserAddress::class)->find($addressId);
    }

    /**
     * @group frontend-ACL
     * @dataProvider aclProvider
     */
    public function testAcl(string $route, string $resource, string $user, int $status)
    {
        $this->loginUser($user);
        /* @var CustomerUser $resource */
        $resource = $this->getReference($resource);

        /** @var CustomerAddress $address */
        $address = $resource->getAddresses()->first();

        $this->client->request(
            'GET',
            $this->getUrl(
                $route,
                ['entityId' => $resource->getId(), 'id' => $address->getId()]
            )
        );
        $response = $this->client->getResponse();
        self::assertHtmlResponseStatusCodeEquals($response, $status);
    }

    public function aclProvider(): array
    {
        return [
            'UPDATE (anonymous user)' => [
                'route' => 'oro_customer_frontend_customer_user_address_update',
                'resource' => LoadCustomerUserAddressACLData::USER_ACCOUNT_1_1_ROLE_LOCAL,
                'user' => '',
                'status' => 401,
            ],
            'UPDATE (user from another customer)' => [
                'route' => 'oro_customer_frontend_customer_user_address_update',
                'resource' => LoadCustomerUserAddressACLData::USER_ACCOUNT_1_1_ROLE_LOCAL,
                'user' => LoadCustomerUserAddressACLData::USER_ACCOUNT_2_ROLE_LOCAL,
                'status' => 403,
            ],
            'UPDATE (user from parent customer : DEEP)' => [
                'route' => 'oro_customer_frontend_customer_user_address_update',
                'resource' => LoadCustomerUserAddressACLData::USER_ACCOUNT_1_1_ROLE_LOCAL,
                'user' => LoadCustomerUserAddressACLData::USER_ACCOUNT_1_ROLE_DEEP,
                'status' => 200,
            ],
            'UPDATE (user from parent customer : LOCAL_VIEW_ONLY)' => [
                'route' => 'oro_customer_frontend_customer_user_address_update',
                'resource' => LoadCustomerUserAddressACLData::USER_ACCOUNT_1_1_ROLE_LOCAL,
                'user' => LoadCustomerUserAddressACLData::USER_ACCOUNT_1_ROLE_DEEP_VIEW_ONLY,
                'status' => 403,
            ],
            'UPDATE (user from same customer : LOCAL_VIEW_ONLY)' => [
                'route' => 'oro_customer_frontend_customer_user_address_update',
                'resource' => LoadCustomerUserAddressACLData::USER_ACCOUNT_1_ROLE_LOCAL,
                'user' => LoadCustomerUserAddressACLData::USER_ACCOUNT_1_ROLE_LOCAL_VIEW_ONLY,
                'status' => 403,
            ],
            'UPDATE (user from same customer : LOCAL)' => [
                'route' => 'oro_customer_frontend_customer_user_address_update',
                'resource' => LoadCustomerUserAddressACLData::USER_ACCOUNT_1_ROLE_DEEP,
                'user' => LoadCustomerUserAddressACLData::USER_ACCOUNT_1_ROLE_LOCAL,
                'status' => 200,
            ],
        ];
    }

    /**
     * @group frontend-ACL
     * @dataProvider gridAclProvider
     */
    public function testGridAcl(
        string $user,
        int $indexResponseStatus,
        int $gridResponseStatus,
        array $data = []
    ) {
        $this->loginUser($user);
        $this->client->request('GET', $this->getUrl('oro_customer_frontend_customer_user_address_index'));
        $this->assertSame($indexResponseStatus, $this->client->getResponse()->getStatusCode());
        $response = $this->client->requestFrontendGrid([
            'gridName' => 'frontend-customer-customer-user-address-grid',
        ]);
        self::assertResponseStatusCodeEquals($response, $gridResponseStatus);
        if (200 === $gridResponseStatus) {
            $result = self::jsonToArray($response->getContent());
            $actual = array_column($result['data'], 'id');
            $actual = array_map('intval', $actual);
            $expected = array_map(
                function ($ref) {
                    return $this->getReference($ref)->getId();
                },
                $data
            );
            sort($expected);
            sort($actual);
            $this->assertEquals($expected, $actual);
        }
    }

    public function gridAclProvider(): array
    {
        return [
            'NOT AUTHORISED' => [
                'user' => '',
                'indexResponseStatus' => 401,
                'gridResponseStatus' => 403,
                'data' => [],
            ],
            'BASIC: own orders' => [
                'user' => LoadCustomerUserAddressACLData::USER_ACCOUNT_1_ROLE_BASIC,
                'indexResponseStatus' => 200,
                'gridResponseStatus' => 200,
                'data' => [
                    LoadCustomerUserAddressesACLData::ADDRESS_ACC_1_USER_BASIC
                ],
            ],
            'DEEP: all siblings and children' => [
                'user' => LoadCustomerUserAddressACLData::USER_ACCOUNT_1_ROLE_DEEP,
                'indexResponseStatus' => 200,
                'gridResponseStatus' => 200,
                'data' => [
                    LoadCustomerUserAddressesACLData::ADDRESS_ACC_1_1_USER_LOCAL,
                    LoadCustomerUserAddressesACLData::ADDRESS_ACC_1_USER_BASIC,
                    LoadCustomerUserAddressesACLData::ADDRESS_ACC_1_USER_LOCAL,
                    LoadCustomerUserAddressesACLData::ADDRESS_ACC_1_USER_DEEP,
                ],
            ],
            'LOCAL: all siblings' => [
                'user' => LoadCustomerUserAddressACLData::USER_ACCOUNT_1_ROLE_LOCAL,
                'indexResponseStatus' => 200,
                'gridResponseStatus' => 200,
                'data' => [
                    LoadCustomerUserAddressesACLData::ADDRESS_ACC_1_USER_BASIC,
                    LoadCustomerUserAddressesACLData::ADDRESS_ACC_1_USER_LOCAL,
                    LoadCustomerUserAddressesACLData::ADDRESS_ACC_1_USER_DEEP,
                ],
            ],
        ];
    }
}
