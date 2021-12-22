<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserRoleData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class AjaxCustomerUserControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
        $this->loadFixtures([LoadCustomerUserRoleData::class]);
    }

    public function testGetCustomerIdAction()
    {
        /** @var CustomerUser $user */
        $user = $this->getUserRepository()->findOneBy(['email' => LoadCustomerUserData::EMAIL]);

        $this->assertNotNull($user);

        $id = $user->getId();

        $this->client->request(
            'GET',
            $this->getUrl('oro_customer_customer_user_get_customer', ['id' => $id])
        );

        $result = $this->client->getResponse();

        $this->assertJsonResponseStatusCodeEquals($result, 200);

        $data = json_decode($result->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('customerId', $data);

        $customerId = $user->getCustomer()?->getId();

        $this->assertEquals($data['customerId'], $customerId);
    }

    private function getUserRepository(): EntityRepository
    {
        return self::getContainer()->get('doctrine')->getRepository(CustomerUser::class);
    }
}
