<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRepository;
use Oro\Bundle\CustomerBundle\Tests\Functional\Api\DataFixtures\LoadTestCustomerUser;

/**
 * @dbIsolationPerTest
 */
class UserCaseInsensitiveEmailTest extends RestJsonApiTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->loadFixtures([LoadTestCustomerUser::class]);
    }

    public function testCreateAndUpdateCaseSensitive()
    {
        if ($this->getRepository()->isCaseInsensitiveCollation()) {
            self::markTestSkipped('Case insensitive email option can\'t be disabled.');
        }

        $this->getConfigManager()->set('oro_customer.case_insensitive_email_addresses_enabled', false);
        $this->getConfigManager()->flush();

        $this->post(['entity' => 'customerusers'], $this->getData());
        $user = $this->assertRequestSuccess($this->getData());

        $data = $this->getData();
        $data['data']['id'] = (string)$user->getId();
        $data['data']['attributes']['username'] = 'NewEmail@Test.Com';
        $data['data']['attributes']['email'] = 'NewEmail@Test.Com';
        $data['data']['attributes']['firstName'] = 'John';
        unset($data['data']['attributes']['password']);

        $this->patch(['entity' => 'customerusers', 'id' => $user->getId()], $data);
        $this->assertRequestSuccess($data);
    }

    public function testCreateAndUpdateCaseInsensitive()
    {
        $this->getConfigManager()->set('oro_customer.case_insensitive_email_addresses_enabled', true);
        $this->getConfigManager()->flush();

        $response = $this->post(['entity' => 'customerusers'], $this->getData(), [], false);

        $this->assertResponseValidationError(
            [
                'title'  => 'unique customer user name and email constraint',
                'detail' => 'This email is already used.',
                'source' => ['pointer' => '/data/attributes/email']
            ],
            $response
        );
        self::assertTrue(null === $this->getUser('Bob', 'Fedeson'));

        $data = $this->getData();
        $data['data']['attributes']['username'] = 'Email@Test.Com';
        $data['data']['attributes']['email'] = 'Email@Test.Com';

        $this->post(['entity' => 'customerusers'], $data);
        $user = $this->assertRequestSuccess($data);

        $data['data']['id'] = (string)$user->getId();
        $data['data']['attributes']['username'] = 'NewEmail@Test.Com';
        $data['data']['attributes']['email'] = 'NewEmail@Test.Com';
        $data['data']['attributes']['firstName'] = 'John';
        unset($data['data']['attributes']['password']);

        $this->patch(['entity' => 'customerusers', 'id' => $user->getId()], $data);
        $this->assertRequestSuccess($data);
    }

    /**
     * @param array $data
     *
     * @return CustomerUser
     */
    private function assertRequestSuccess(array $data): CustomerUser
    {
        $data = $data['data']['attributes'];
        $user = $this->getUser($data['firstName'], $data['lastName']);

        self::assertNotNull($user);
        self::assertEquals($data['username'], $user->getUsername());
        self::assertEquals($data['email'], $user->getEmail());

        return $user;
    }

    /**
     * @param string $firstName
     * @param string $lastName
     *
     * @return CustomerUser|null
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
     * @return ConfigManager
     */
    private function getConfigManager(): ConfigManager
    {
        return self::getContainer()->get('oro_config.global');
    }

    /**
     * @return array
     */
    private function getData(): array
    {
        return [
            'data' => [
                'type'          => 'customerusers',
                'attributes'    => [
                    'username'  => 'Test@Test.Com',
                    'email'     => 'Test@Test.Com',
                    'password'  => 'Password!123',
                    'firstName' => 'Bob',
                    'lastName'  => 'Fedeson'
                ],
                'relationships' => [
                    'customer' => [
                        'data' => [
                            'type' => 'customers',
                            'id'   => '<toString(@testCustomerUser->customer->id)>'
                        ]
                    ],
                    'roles'    => [
                        'data' => [
                            [
                                'type' => 'customeruserroles',
                                'id'   => '<toString(@testCustomerUser->roles[0]->id)>'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
