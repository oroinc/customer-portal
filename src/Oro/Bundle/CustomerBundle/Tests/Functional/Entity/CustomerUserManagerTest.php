<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Entity;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CustomerUserManagerTest extends WebTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader(LoadCustomerUserData::EMAIL, LoadCustomerUserData::PASSWORD)
        );
        $this->loadFixtures([LoadCustomerUserData::class]);
    }

    public function testUserReloadWhenEntityIsChangedByReference()
    {
        // init tokens
        $this->client->request('GET', $this->getUrl('oro_customer_frontend_customer_user_profile'));
        $this->assertHtmlResponseStatusCodeEquals($this->client->getResponse(), 200);

        /** @var CustomerUser $loggedUser */
        $loggedUser = $this->getContainer()->get('oro_security.security_facade')->getLoggedUser();
        $originalId = $loggedUser->getId();
        $this->assertInstanceOf(CustomerUser::class, $loggedUser);
        $this->assertSame(LoadCustomerUserData::EMAIL, $loggedUser->getUsername(), 'logged user email');

        /** @var CustomerUser $customerUser */
        $customerUser = $this->getReference(LoadCustomerUserData::EMAIL);
        $customerUser->setEmail(LoadCustomerUserData::LEVEL_1_EMAIL);
        $this->assertSame(LoadCustomerUserData::LEVEL_1_EMAIL, $loggedUser->getUsername(), 'email after change');
        $this->assertSame($originalId, $customerUser->getId());
        $this->assertSame($originalId, $loggedUser->getId());

        /** @var CustomerUserManager $customerUserManager */
        $customerUserManager = $this->getContainer()->get('oro_customer_user.manager');
        $customerUserManager->refreshUser($customerUser);

        $this->assertSame(LoadCustomerUserData::EMAIL, $loggedUser->getUsername(), 'email after refresh');
        $this->assertSame($originalId, $loggedUser->getId());
    }

    public function testReloadUserWithNotManagedEntity()
    {
        // init tokens
        $this->client->request('GET', $this->getUrl('oro_customer_frontend_customer_user_profile'));
        $this->assertHtmlResponseStatusCodeEquals($this->client->getResponse(), 200);

        /** @var CustomerUser $loggedUser */
        $loggedUser = $this->getContainer()->get('oro_security.security_facade')->getLoggedUser();
        $em = $this->getContainer()->get('doctrine')->getManagerForClass(ClassUtils::getClass($loggedUser));

        $originalId = $loggedUser->getId();
        $this->assertInstanceOf(CustomerUser::class, $loggedUser);
        $this->assertSame(LoadCustomerUserData::EMAIL, $loggedUser->getUsername(), 'logged user email');

        $loggedUser->setEmail(LoadCustomerUserData::LEVEL_1_EMAIL);
        $em->detach($loggedUser);

        /** @var CustomerUserManager $customerUserManager */
        $customerUserManager = $this->getContainer()->get('oro_customer_user.manager');
        $loggedUser = $customerUserManager->refreshUser($loggedUser);

        $this->assertSame(LoadCustomerUserData::EMAIL, $loggedUser->getUsername(), 'email after refresh');
        $this->assertSame($originalId, $loggedUser->getId());
    }
}
