<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api;

use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\ConfigBundle\Config\GlobalScopeManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRepository;
use Oro\Bundle\CustomerBundle\Tests\Functional\Api\DataFixtures\LoadTestCustomerUser;
use Symfony\Component\HttpFoundation\Response;

/**
 * @dbIsolationPerTest
 */
class UserCaseInsensitiveEmailTest extends RestJsonApiTestCase
{
    /** @var GlobalScopeManager */
    private $configManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([LoadTestCustomerUser::class]);

        $this->configManager = $this
            ->getClientInstance()
            ->getContainer()
            ->get('oro_config.global');
    }

    public function testCreateAndUpdateCaseSensitive()
    {
        if ($this->getRepository()->isCaseInsensitiveCollation()) {
            $this->markTestSkipped('Case insensitive email option can\'t be disabled.');
        }

        $this->configManager->set('oro_customer.case_insensitive_email_addresses_enabled', false);
        $this->configManager->flush();

        $entityType = $this->getEntityType(CustomerUser::class);

        $response = $this->post(['entity' => $entityType], $this->getData());
        $user = $this->assertRequestSuccess($response, $this->getData(), Response::HTTP_CREATED);

        $data = $this->getData();
        $data['data']['id'] = (string)$user->getId();
        $data['data']['attributes']['username'] = 'NewEmail@Test.Com';
        $data['data']['attributes']['email'] = 'NewEmail@Test.Com';
        $data['data']['attributes']['firstName'] = 'John';
        unset($data['data']['attributes']['password']);

        $response = $this->patch(['entity' => $entityType, 'id' => $user->getId()], $data);
        $this->assertRequestSuccess($response, $data, Response::HTTP_OK);
    }

    public function testCreateAndUpdateCaseInsensitive()
    {
        $this->configManager->set('oro_customer.case_insensitive_email_addresses_enabled', true);
        $this->configManager->flush();

        $entityType = $this->getEntityType(CustomerUser::class);

        $response = $this->post(['entity' => $entityType], $this->getData(), [], false);

        static::assertResponseStatusCodeEquals($response, Response::HTTP_BAD_REQUEST);
        static::assertContains('unique customer user name and email constraint', $response->getContent());
        static::assertTrue(null === $this->getUser('Bob', 'Fedeson'));

        $data = $this->getData();
        $data['data']['attributes']['username'] = 'Email@Test.Com';
        $data['data']['attributes']['email'] = 'Email@Test.Com';

        $response = $this->post(['entity' => $entityType], $data);
        $user = $this->assertRequestSuccess($response, $data, Response::HTTP_CREATED);

        $data['data']['id'] = (string)$user->getId();
        $data['data']['attributes']['username'] = 'NewEmail@Test.Com';
        $data['data']['attributes']['email'] = 'NewEmail@Test.Com';
        $data['data']['attributes']['firstName'] = 'John';
        unset($data['data']['attributes']['password']);

        $response = $this->patch(['entity' => $entityType, 'id' => $user->getId()], $data);
        $this->assertRequestSuccess($response, $data, Response::HTTP_OK);
    }

    /**
     * @param Response $response
     * @param array $data
     * @param int $expectedCode
     * @return CustomerUser
     */
    private function assertRequestSuccess(Response $response, array $data, int $expectedCode): CustomerUser
    {
        static::assertResponseStatusCodeEquals($response, $expectedCode);

        $data = $data['data']['attributes'];
        $user = $this->getUser($data['firstName'], $data['lastName']);

        static::assertNotNull($user);
        static::assertEquals($data['username'], $user->getUsername());
        static::assertEquals($data['email'], $user->getEmail());

        return $user;
    }

    /**
     * @param string $firstName
     * @param string $lastName
     * @return null|CustomerUser
     */
    private function getUser(string $firstName, string $lastName): ?CustomerUser
    {
        return $this->getRepository()
            ->findOneBy(['firstName' => $firstName, 'lastName' => $lastName]);
    }

    /**
     * @return CustomerUserRepository
     */
    private function getRepository(): CustomerUserRepository
    {
        return $this->getEntityManager()
            ->getRepository(CustomerUser::class);
    }

    /**
     * @return array
     */
    private function getData(): array
    {
        /** @var CustomerUser $customerUser */
        $customerUser = $this->getReference('testCustomerUser');
        $customer = $customerUser->getCustomer();
        $role = $this->getContainer()->get('doctrine')->getRepository(CustomerUserRole::class)->find(1);

        return [
            'data' => [
                'type' => $this->getEntityType(CustomerUser::class),
                'attributes' => [
                    'username' => 'Test@Test.Com',
                    'email' => 'Test@Test.Com',
                    'password' => 'Password!123',
                    'firstName' => 'Bob',
                    'lastName' => 'Fedeson',
                ],
                'relationships' => [
                    'customer' => [
                        'data' => [
                            'type' => 'customers',
                            'id' => (string)$customer->getId(),
                        ],
                    ],
                    'roles' => [
                        'data' => [
                            [
                                'type' => 'customer_user_roles',
                                'id' => (string)$role->getId(),
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
