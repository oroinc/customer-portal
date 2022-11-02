<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRepository;
use Oro\Bundle\CustomerBundle\Tests\Functional\Api\DataFixtures\LoadTestCustomerUser;

/**
 * @group CommunityEdition
 *
 * @dbIsolationPerTest
 */
class UserCaseInsensitiveEmailTest extends RestJsonApiTestCase
{
    use ConfigManagerAwareTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([LoadTestCustomerUser::class]);
    }

    private function setCaseInsensitiveEmailAddresses(bool $value)
    {
        $configManager = self::getConfigManager('global');
        $configManager->set('oro_customer.case_insensitive_email_addresses_enabled', $value);
        $configManager->flush();
    }

    private function getCustomerUserRepository(): CustomerUserRepository
    {
        return $this->getEntityManager()->getRepository(CustomerUser::class);
    }

    private function findCustomerUser(string $firstName, string $lastName): ?CustomerUser
    {
        return $this->getCustomerUserRepository()
            ->findOneBy(['firstName' => $firstName, 'lastName' => $lastName]);
    }

    protected function getData(): array
    {
        return [
            'data' => [
                'type'          => 'customerusers',
                'attributes'    => [
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
                    'userRoles'    => [
                        'data' => [
                            [
                                'type' => 'customeruserroles',
                                'id'   => '<toString(@testCustomerUser->userRoles[0]->id)>'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    private function assertRequestSuccess(array $data): CustomerUser
    {
        $attributes = $data['data']['attributes'];

        $user = $this->findCustomerUser($attributes['firstName'], $attributes['lastName']);
        self::assertNotNull($user);
        self::assertEquals($attributes['email'], $user->getEmail());
        self::assertEquals($user->getEmail(), $user->getUsername());

        return $user;
    }

    public function testCreateAndUpdateCaseSensitive()
    {
        if ($this->getCustomerUserRepository()->isCaseInsensitiveCollation()) {
            self::markTestSkipped('Case insensitive email option cannot be disabled.');
        }

        $this->setCaseInsensitiveEmailAddresses(false);

        $data = $this->getData();
        $this->post(['entity' => 'customerusers'], $data);
        $user = $this->assertRequestSuccess($data);

        $data['data']['id'] = (string)$user->getId();
        $data['data']['attributes']['email'] = 'NewEmail@Test.Com';
        $data['data']['attributes']['firstName'] = 'John';
        unset($data['data']['attributes']['password']);
        $this->patch(['entity' => 'customerusers', 'id' => $user->getId()], $data);
        $this->assertRequestSuccess($data);
    }

    public function testCreateAndUpdateCaseInsensitive()
    {
        $this->setCaseInsensitiveEmailAddresses(true);

        $data = $this->getData();
        $response = $this->post(['entity' => 'customerusers'], $data, [], false);
        $this->assertResponseValidationError(
            [
                'title'  => 'unique customer user name and email constraint',
                'detail' => 'This email is already used.',
                'source' => ['pointer' => '/data/attributes/email']
            ],
            $response
        );
        self::assertTrue(
            null === $this->findCustomerUser(
                $data['data']['attributes']['firstName'],
                $data['data']['attributes']['lastName']
            )
        );

        $data['data']['attributes']['email'] = 'Email@Test.Com';
        $this->post(['entity' => 'customerusers'], $data);
        $user = $this->assertRequestSuccess($data);

        $data['data']['id'] = (string)$user->getId();
        $data['data']['attributes']['email'] = 'NewEmail@Test.Com';
        $data['data']['attributes']['firstName'] = 'John';
        unset($data['data']['attributes']['password']);
        $this->patch(['entity' => 'customerusers', 'id' => $user->getId()], $data);
        $this->assertRequestSuccess($data);
    }

    public function testFindCustomerUserByEmail()
    {
        $this->setCaseInsensitiveEmailAddresses(true);
        $response = $this->cget(['entity' => 'customerusers'], [
            'filter[email]' => 'Test@test.com'
        ]);
        $content = self::jsonToArray($response->getContent());
        $this->assertNotEmpty($content);
        $this->assertArrayHasKey('data', $content);
        $this->assertIsArray($content['data']);
        $this->assertCount(1, $content['data']);
        $this->assertEquals('test@test.com', $content['data'][0]['attributes']['email']);

        $this->setCaseInsensitiveEmailAddresses(false);
        $response = $this->cget(['entity' => 'customerusers'], [
            'filter[email]' => 'Test@test.com'
        ]);
        $this->assertResponseCount(0, $response);
    }
}
