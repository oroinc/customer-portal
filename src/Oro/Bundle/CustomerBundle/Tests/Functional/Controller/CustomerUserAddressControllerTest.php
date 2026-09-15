<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller;

use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Test\Functional\RolePermissionExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadRolesData;
use Oro\Bundle\UserBundle\Tests\Functional\Api\DataFixtures\LoadUserData;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\HttpFoundation\Response;

class CustomerUserAddressControllerTest extends WebTestCase
{
    use RolePermissionExtension;

    /** @var CustomerUser */
    private $customerUser;

    #[\Override]
    protected function setUp(): void
    {
        $this->initClient([], array_merge(self::generateBasicAuthHeader()));
        $this->loadFixtures([LoadCustomerUserData::class, LoadUserData::class]);

        $this->customerUser = $this->getReference(LoadCustomerUserData::EMAIL);
    }

    public function testCustomerUserView()
    {
        $this->client->request('GET', $this->getUrl('oro_customer_customer_user_view', [
            'id' => $this->customerUser->getId()
        ]));

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $content = $result->getContent();

        self::assertStringContainsString('Address Book', $content);
    }

    /**
     * @depends testCustomerUserView
     */
    public function testCreateAddress(): int
    {
        $customerUser = $this->customerUser;
        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_customer_customer_user_address_create',
                ['entityId' => $customerUser->getId(), '_widgetContainer' => 'dialog']
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
            $this->getUrl('oro_api_customer_get_customeruser_address_primary', [
                'entityId' => $customerUser->getId()
            ]),
            [],
            [],
            self::generateApiAuthHeader()
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);
        sort($result['types']);

        $this->assertEquals('Badakhshān', $result['region']);
        $this->assertEquals([
            [
                'name'  => AddressType::TYPE_BILLING,
                'label' => ucfirst(AddressType::TYPE_BILLING)
            ]
        ], $result['types']);

        $this->assertEquals([
            [
                'name'  => AddressType::TYPE_BILLING,
                'label' => ucfirst(AddressType::TYPE_BILLING)
            ]
        ], $result['defaults']);

        return $customerUser->getId();
    }

    /**
     * @depends testCreateAddress
     */
    public function testUpdateAddress(int $customerUserId): int
    {
        $this->client->request(
            'GET',
            $this->getUrl('oro_api_customer_get_customeruser_address_primary', [
                'entityId' => $customerUserId
            ]),
            [],
            [],
            self::generateApiAuthHeader()
        );

        $address = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_customer_customer_user_address_update',
                ['entityId' => $customerUserId, 'id' => $address['id'], '_widgetContainer' => 'dialog']
            )
        );

        $result = $this->client->getResponse();
        $this->assertEquals(200, $result->getStatusCode());

        $form = $crawler->selectButton('Save')->form();
        $this->fillFormForUpdateTest($form);

        $this->client->followRedirects(true);
        $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $this->client->request(
            'GET',
            $this->getUrl('oro_api_customer_get_customeruser_address_primary', [
                'entityId' => $customerUserId
            ]),
            [],
            [],
            self::generateApiAuthHeader()
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);
        sort($result['types']);

        $this->assertEquals('Manicaland', $result['region']);
        $this->assertEquals([
            [
                'name'  => AddressType::TYPE_BILLING,
                'label' => ucfirst(AddressType::TYPE_BILLING)
            ],
            [
                'name'  => AddressType::TYPE_SHIPPING,
                'label' => ucfirst(AddressType::TYPE_SHIPPING)
            ]
        ], $result['types']);

        $this->assertEquals([
            [
                'name'  => AddressType::TYPE_SHIPPING,
                'label' => ucfirst(AddressType::TYPE_SHIPPING)
            ]
        ], $result['defaults']);

        return $customerUserId;
    }

    /**
     * @depends testUpdateAddress
     */
    public function testPrimaryAndByTypeAddressAcl(int $customerUserId): void
    {
        $this->updateRolePermission(
            LoadRolesData::ROLE_USER,
            CustomerUser::class,
            AccessLevel::GLOBAL_LEVEL
        );

        $routes = [
            ['oro_api_customer_get_customeruser_address_primary', []],
            ['oro_api_customer_get_customeruser_address_by_type', ['typeName' => AddressType::TYPE_BILLING]],
        ];
        $accessLevels = [
            [AccessLevel::GLOBAL_LEVEL, Response::HTTP_OK],
            [AccessLevel::BASIC_LEVEL, Response::HTTP_FORBIDDEN],
        ];

        foreach ($accessLevels as [$accessLevel, $expectedStatusCode]) {
            $this->updateRolePermission(
                LoadRolesData::ROLE_USER,
                CustomerUserAddress::class,
                $accessLevel
            );

            foreach ($routes as [$routeName, $routeParameters]) {
                $this->client->jsonRequest(
                    'GET',
                    $this->getUrl(
                        $routeName,
                        array_merge(['entityId' => $customerUserId], $routeParameters)
                    ),
                    [],
                    self::generateApiAuthHeader(LoadUserData::USER_NAME_2)
                );

                self::assertJsonResponseStatusCodeEquals(
                    $this->client->getResponse(),
                    $expectedStatusCode
                );
                if (Response::HTTP_OK === $expectedStatusCode) {
                    $result = self::getJsonResponseContent($this->client->getResponse(), Response::HTTP_OK);
                    self::assertEquals('Manicaland', $result['region']);
                }
            }
        }
    }

    /**
     * Fill form for address tests (create test)
     */
    private function fillFormForCreateTest(Form $form): Form
    {
        $formNode = $form->getNode();
        $formNode->setAttribute('action', $formNode->getAttribute('action') . '?_widgetContainer=dialog');

        $form['oro_customer_customer_user_typed_address[street]'] = 'Street';
        $form['oro_customer_customer_user_typed_address[city]'] = 'City';
        $form['oro_customer_customer_user_typed_address[postalCode]'] = 'Zip code';
        $form['oro_customer_customer_user_typed_address[types]'] = [AddressType::TYPE_BILLING];
        $form['oro_customer_customer_user_typed_address[defaults][default]'] = [AddressType::TYPE_BILLING];

        $doc = new \DOMDocument('1.0');
        $doc->loadHTML(
            '<select name="oro_customer_customer_user_typed_address[country]" ' .
            'id="oro_customer_customer_user_typed_address_country" ' .
            'tabindex="-1" class="select2-offscreen"> ' .
            '<option value="" selected="selected"></option> ' .
            '<option value="AF">Afghanistan</option> </select>'
        );
        $field = new ChoiceFormField($doc->getElementsByTagName('select')->item(0));
        $form->set($field);
        $form['oro_customer_customer_user_typed_address[country]'] = 'AF';

        $doc->loadHTML(
            '<select name="oro_customer_customer_user_typed_address[region]" ' .
            'id="oro_customer_customer_user_typed_address_region" ' .
            'tabindex="-1" class="select2-offscreen"> ' .
            '<option value="" selected="selected"></option> ' .
            '<option value="AF-BDS">Badakhshān</option> </select>'
        );
        $field = new ChoiceFormField($doc->getElementsByTagName('select')->item(0));
        $form->set($field);
        $form['oro_customer_customer_user_typed_address[region]'] = 'AF-BDS';

        return $form;
    }

    /**
     * Fill form for address tests (update test)
     */
    private function fillFormForUpdateTest(Form $form): Form
    {
        $formNode = $form->getNode();
        $formNode->setAttribute('action', $formNode->getAttribute('action') . '?_widgetContainer=dialog');

        $form['oro_customer_customer_user_typed_address[types]'] = [
            AddressType::TYPE_BILLING,
            AddressType::TYPE_SHIPPING
        ];
        $form['oro_customer_customer_user_typed_address[defaults][default]'] = [false, AddressType::TYPE_SHIPPING];

        $doc = new \DOMDocument('1.0');
        $doc->loadHTML(
            '<select name="oro_customer_customer_user_typed_address[country]" ' .
            'id="oro_customer_customer_user_typed_address_country" ' .
            'tabindex="-1" class="select2-offscreen"> ' .
            '<option value="" selected="selected"></option> ' .
            '<option value="ZW">Zimbabwe</option> </select>'
        );
        $field = new ChoiceFormField($doc->getElementsByTagName('select')->item(0));
        $form->set($field);
        $form['oro_customer_customer_user_typed_address[country]'] = 'ZW';

        $doc->loadHTML(
            '<select name="oro_customer_customer_user_typed_address[region]" ' .
            'id="oro_customer_customer_user_typed_address_region" ' .
            'tabindex="-1" class="select2-offscreen"> ' .
            '<option value="" selected="selected"></option> ' .
            '<option value="ZW-MA">Manicaland</option> </select>'
        );
        $field = new ChoiceFormField($doc->getElementsByTagName('select')->item(0));
        $form->set($field);
        $form['oro_customer_customer_user_typed_address[region]'] = 'ZW-MA';

        return $form;
    }
}
