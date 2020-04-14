<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Security;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\UserBundle\Security\UserProvider;

class UserProviderTest extends WebTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader(LoadCustomerUserData::EMAIL, LoadCustomerUserData::PASSWORD)
        );
        $this->loadFixtures([LoadCustomerUserData::class]);
    }

    private function refreshUser($user)
    {
        /** @var UserProvider $userProvider */
        $userProvider = $this->getContainer()->get('oro_customer.tests.security.provider');

        return $userProvider->refreshUser($user);
    }

    public function testUserReloadWhenEntityIsChangedByReference()
    {
        // init tokens
        $this->client->request('GET', $this->getUrl('oro_customer_frontend_customer_user_profile'));
        $this->assertHtmlResponseStatusCodeEquals($this->client->getResponse(), 200);

        /** @var CustomerUser $loggedUser */
        $loggedUser = $this->getContainer()->get('oro_security.token_accessor')->getUser();
        $originalId = $loggedUser->getId();
        $this->assertInstanceOf(CustomerUser::class, $loggedUser);
        $this->assertSame(LoadCustomerUserData::EMAIL, $loggedUser->getUsername(), 'logged user email');

        /** @var CustomerUser $customerUser */
        $customerUser = $this->getReference(LoadCustomerUserData::EMAIL);
        $customerUser->setEmail(LoadCustomerUserData::LEVEL_1_EMAIL);
        $this->assertSame(LoadCustomerUserData::LEVEL_1_EMAIL, $loggedUser->getUsername(), 'email after change');
        $this->assertSame($originalId, $customerUser->getId());
        $this->assertSame($originalId, $loggedUser->getId());

        $this->refreshUser($customerUser);

        $this->assertSame(LoadCustomerUserData::EMAIL, $loggedUser->getUsername(), 'email after refresh');
        $this->assertSame($originalId, $loggedUser->getId());
    }

    public function testReloadUserWithNotManagedEntity()
    {
        // init tokens
        $this->client->request('GET', $this->getUrl('oro_customer_frontend_customer_user_profile'));
        $this->assertHtmlResponseStatusCodeEquals($this->client->getResponse(), 200);

        /** @var CustomerUser $loggedUser */
        $loggedUser = $this->getContainer()->get('oro_security.token_accessor')->getUser();
        $em = $this->getContainer()->get('doctrine')->getManagerForClass(ClassUtils::getClass($loggedUser));

        $originalId = $loggedUser->getId();
        $this->assertInstanceOf(CustomerUser::class, $loggedUser);
        $this->assertSame(LoadCustomerUserData::EMAIL, $loggedUser->getUsername(), 'logged user email');

        $loggedUser->setEmail(LoadCustomerUserData::LEVEL_1_EMAIL);
        $em->detach($loggedUser);

        $loggedUser = $this->refreshUser($loggedUser);

        $this->assertSame(LoadCustomerUserData::EMAIL, $loggedUser->getUsername(), 'email after refresh');
        $this->assertSame($originalId, $loggedUser->getId());
    }
}
