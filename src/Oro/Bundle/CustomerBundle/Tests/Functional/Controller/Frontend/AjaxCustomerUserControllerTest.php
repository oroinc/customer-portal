<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller\Frontend;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserRoleData;
use Oro\Bundle\FrontendTestFrameworkBundle\Migrations\Data\ORM\LoadCustomerUserData as LoadLoginCustomerUserData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class AjaxCustomerUserControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader(LoadLoginCustomerUserData::AUTH_USER, LoadLoginCustomerUserData::AUTH_PW)
        );
        $this->client->useHashNavigation(true);
        $this->loadFixtures([LoadCustomerUserRoleData::class]);
    }

    public function testGetCustomerIdAction()
    {
        /** @var CustomerUser $user */
        $user = $this->getUserRepository()->findOneBy(['email' => 'customer.user2@test.com']);
        $this->assertNotNull($user);
        $id = $user->getId();
        $this->client->request(
            'GET',
            $this->getUrl('oro_customer_frontend_customer_user_get_customer', ['id' => $id])
        );
        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 200);
        $data = json_decode($result->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('customerId', $data);
        $customerId = $user->getCustomer()?->getId();
        $this->assertEquals($data['customerId'], $customerId);
    }

    /**
     * @dataProvider validateDataProvider
     */
    public function testValidate(string $value, bool $expected)
    {
        $this->client->request(
            'POST',
            $this->getUrl('oro_customer_frontend_customer_user_validate', ['value' => $value])
        );
        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 200);
        $data = json_decode($result->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('valid', $data);
        $this->assertEquals($expected, $data['valid']);
    }

    public function validateDataProvider(): array
    {
        return [
            [LoadLoginCustomerUserData::AUTH_USER, false],
            ['unknown@test.com', true]
        ];
    }

    private function getObjectManager(): EntityManagerInterface
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    private function getUserRepository(): EntityRepository
    {
        return $this->getObjectManager()->getRepository(CustomerUser::class);
    }
}
