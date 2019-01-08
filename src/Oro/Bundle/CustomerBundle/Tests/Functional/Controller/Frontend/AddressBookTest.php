<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller\Frontend;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadAddressBookUserData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class AddressBookTest extends WebTestCase
{
    /**
     * @var CustomerUser
     */
    protected $currentUser;

    public function testAddressBookMenuItemHidden()
    {
        $this->initAddressBookClient(
            LoadAddressBookUserData::ACCOUNT1_USER4,
            LoadAddressBookUserData::ACCOUNT1_USER4
        );

        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_customer_frontend_customer_user_profile')
        );
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertFalse($this->isAddressBookMenuVisible($crawler));

        $this->client->request(
            'GET',
            $this->getUrl('oro_customer_frontend_customer_user_address_index')
        );
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
        $this->assertFalse($this->isAddressBookMenuVisible($crawler));
    }

    public function testCustomerAddressView()
    {
        $this->initAddressBookClient(
            LoadAddressBookUserData::ACCOUNT1_USER1,
            LoadAddressBookUserData::ACCOUNT1_USER1
        );

        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_customer_frontend_customer_user_address_index')
        );
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->assertFalse($this->isAddUserAddressButtonVisible($crawler));
        $this->assertFalse($this->isAddCustomerAddressButtonVisible($crawler));
        $this->assertFalse($this->isCustomerUserAddressSectionVisible($crawler));

        $this->assertTrue($this->isAddressBookMenuVisible($crawler));
        $this->assertTrue($this->isCustomerAddressSectionVisible($crawler));
    }

    public function testCustomerAndCustomerUserAddressView()
    {
        $this->initAddressBookClient(
            LoadAddressBookUserData::ACCOUNT1_USER3,
            LoadAddressBookUserData::ACCOUNT1_USER3
        );

        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_customer_frontend_customer_user_address_index')
        );
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertFalse($this->isAddUserAddressButtonVisible($crawler));
        $this->assertFalse($this->isAddCustomerAddressButtonVisible($crawler));

        $this->assertTrue($this->isCustomerUserAddressSectionVisible($crawler));
        $this->assertTrue($this->isAddressBookMenuVisible($crawler));
        $this->assertTrue($this->isCustomerAddressSectionVisible($crawler));
    }

    public function testCustomerUserAddressView()
    {
        $this->initAddressBookClient(
            LoadAddressBookUserData::ACCOUNT1_USER2,
            LoadAddressBookUserData::ACCOUNT1_USER2
        );

        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_customer_frontend_customer_user_address_index')
        );
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->assertFalse($this->isAddUserAddressButtonVisible($crawler));
        $this->assertFalse($this->isAddCustomerAddressButtonVisible($crawler));
        $this->assertFalse($this->isCustomerAddressSectionVisible($crawler));

        $this->assertTrue($this->isAddressBookMenuVisible($crawler));
        $this->assertTrue($this->isCustomerUserAddressSectionVisible($crawler));
    }

    public function testCustomerAddressCreateButton()
    {
        $this->initAddressBookClient(
            LoadAddressBookUserData::ACCOUNT1_USER6,
            LoadAddressBookUserData::ACCOUNT1_USER6
        );

        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_customer_frontend_customer_user_address_index')
        );
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->assertFalse($this->isAddUserAddressButtonVisible($crawler));
        $this->assertFalse($this->isCustomerUserAddressSectionVisible($crawler));

        $this->assertTrue($this->isCustomerAddressSectionVisible($crawler));
        $this->assertTrue($this->isAddressBookMenuVisible($crawler));
    }

    public function testCustomerUserAddressCreateButton()
    {
        $this->initAddressBookClient(
            LoadAddressBookUserData::ACCOUNT1_USER7,
            LoadAddressBookUserData::ACCOUNT1_USER7
        );

        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_customer_frontend_customer_user_address_index')
        );
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->assertFalse($this->isCustomerAddressSectionVisible($crawler));
        $this->assertFalse($this->isAddCustomerAddressButtonVisible($crawler));

        $this->assertTrue($this->isCustomerUserAddressSectionVisible($crawler));
        $this->assertTrue($this->isAddressBookMenuVisible($crawler));
    }

    /**
     * @param string $username
     * @param string $password
     */
    protected function initAddressBookClient($username, $password)
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader($username, $password)
        );

        $this->loadFixtures([LoadAddressBookUserData::class]);
    }

    /**
     * @param Crawler $crawler
     * @return bool
     */
    protected function isAddUserAddressButtonVisible(Crawler $crawler)
    {
        return $crawler->selectLink('New Address')->count() > 0;
    }

    /**
     * @param Crawler $crawler
     * @return bool
     */
    protected function isAddCustomerAddressButtonVisible(Crawler $crawler)
    {
        return $crawler->selectLink('New Company Address')->count() > 0;
    }

    /**
     * @return bool
     */
    protected function isCustomerUserAddressSectionVisible(Crawler $crawler)
    {
        return $crawler->filter('[data-page-component-name=frontend-customer-customer-user-address-grid]')->count() > 0;
    }

    /**
     * @return bool
     */
    protected function isCustomerAddressSectionVisible(Crawler $crawler)
    {
        return $crawler->filter('[data-page-component-name=frontend-customer-customer-address-grid]')->count() > 0;
    }

    /**
     * @param Crawler $crawler
     * @return bool
     */
    protected function isAddressBookMenuVisible(Crawler $crawler)
    {
        return $crawler->selectLink('Address Book')->count() > 0;
    }
}
