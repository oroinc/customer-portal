<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Entity;

use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerVisitors;
use Symfony\Component\BrowserKit\Cookie;

use Oro\Bundle\AddressBundle\Tests\Unit\Entity\Stub\AddressStub;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class GuestCustomerUserManagerTest extends WebTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->initClient();
        $this->loadFixtures([LoadCustomerVisitors::class]);
    }

    public function testCreateFromAddress()
    {
        // init tokens
        $this->client->request('GET', '/');
        $this->assertHtmlResponseStatusCodeEquals($this->client->getResponse(), 200);

        $requestStack = $this->getContainer()->get('request_stack');
        $requestStack->push($this->client->getRequest());

        $customerUserManager = $this->getContainer()->get('oro_customer.manager.guest_customer_user');
        $websiteManager = $this->getContainer()->get('oro_website.manager');
        $customerUserRelationsProvider = $this->getContainer()
            ->get('oro_customer.provider.customer_user_relations_provider');
        $tokenStorage = $this->getContainer()->get('security.token_storage');

        $email = 'test@example.com';
        $address = new AddressStub();
        $address->setNamePrefix('name prefix');
        $address->setFirstName('first name');
        $address->setLastName('last name');
        $address->setMiddleName('middle name');
        $address->setLastName('last name');
        $address->setNameSuffix('name suffix');

        $expectedCustomerUser = $customerUserManager->createFromAddress($email, $address);

        $this->assertTrue($expectedCustomerUser->isGuest());
        $this->assertFalse($expectedCustomerUser->isEnabled());
        $this->assertFalse($expectedCustomerUser->isConfirmed());
        $this->assertEquals($email, $expectedCustomerUser->getEmail());
        $this->assertEquals($address->getNamePrefix(), $expectedCustomerUser->getNamePrefix());
        $this->assertEquals($address->getFirstName(), $expectedCustomerUser->getFirstName());
        $this->assertEquals($address->getMiddleName(), $expectedCustomerUser->getMiddleName());
        $this->assertEquals($address->getLastName(), $expectedCustomerUser->getLastName());
        $this->assertEquals($address->getNameSuffix(), $expectedCustomerUser->getNameSuffix());
        $this->assertNotEmpty($expectedCustomerUser->getPassword());

        $website = $websiteManager->getCurrentWebsite();

        $this->assertEquals($website, $expectedCustomerUser->getWebsite());
        $this->assertEquals($website->getOrganization(), $expectedCustomerUser->getOrganization());

        $expectedCustomer = $expectedCustomerUser->getCustomer();
        $this->assertEquals($website->getOrganization(), $expectedCustomer->getOrganization());
        $this->assertEquals(
            sprintf('%s %s', $expectedCustomerUser->getFirstName(), $expectedCustomerUser->getLastName()),
            $expectedCustomer->getName()
        );
        $this->assertEquals($customerUserRelationsProvider->getCustomerGroup(), $expectedCustomer->getGroup());

        /** @var AnonymousCustomerUserToken $token */
        $token = $tokenStorage->getToken();
        $this->assertEquals(
            $token->getVisitor()->getCustomerUser()->getFirstName(),
            $expectedCustomerUser->getFirstName()
        );
    }
}
