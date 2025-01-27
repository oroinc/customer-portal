<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller\Frontend\AddressValidation;

use Oro\Bundle\AddressValidationBundle\Test\AddressValidationFeatureAwareTrait;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUser;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserAddresses;
use Oro\Bundle\FrontendTestFrameworkBundle\Migrations\Data\ORM\LoadCustomerUserData;
use Oro\Bundle\IntegrationBundle\Tests\Functional\DataFixtures\LoadChannelData;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Test\Functional\RolePermissionExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @dbIsolationPerTest
 */
final class CustomerUserAddressPageAddressValidationControllerTest extends WebTestCase
{
    use RolePermissionExtension;
    use AddressValidationFeatureAwareTrait;

    #[\Override]
    protected function setUp(): void
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader(
                LoadCustomerUserData::AUTH_USER,
                LoadCustomerUserData::AUTH_PW
            )
        );

        $this->loadFixtures([
            LoadCustomerUser::class,
            LoadCustomerUserAddresses::class,
            LoadChannelData::class
        ]);
    }

    public function testThatRouteNotFoundWhenFeatureDisabled(): void
    {
        $this->ajaxRequest(
            Request::METHOD_POST,
            $this->getUrl(
                'oro_customer_frontend_address_validation_customer_user_address',
                [
                    'customer_user_id' => $this->getReference(LoadCustomerUser::CUSTOMER_USER)->getId(),
                    'id' => 0
                ]
            )
        );

        self::assertResponseStatusCodeEquals($this->client->getResponse(), 404);
    }

    /**
     * @dataProvider aclProvider
     */
    public function testAddressValidationAclForCreating(string $routeName, int $level, int $statusCode): void
    {
        self::enableAddressValidationFeature(
            $this->getReference('oro_integration:foo_integration')->getId()
        );

        $this->updateRolePermissions(
            'ROLE_FRONTEND_ADMINISTRATOR',
            CustomerUserAddress::class,
            [
                'CREATE' => $level,
            ]
        );

        $this->ajaxRequest(
            Request::METHOD_POST,
            $this->getUrl($routeName, [
                'customer_user_id' => $this->getReference(LoadCustomerUser::CUSTOMER_USER)->getId(),
                'id' => 0
            ])
        );

        self::assertResponseStatusCodeEquals($this->client->getResponse(), $statusCode);
    }

    /**
     * @dataProvider aclProvider
     */
    public function testAddressValidationAclForUpdating(string $routeName, int $level, int $statusCode): void
    {
        self::enableAddressValidationFeature(
            $this->getReference('oro_integration:foo_integration')->getId()
        );

        $this->updateRolePermissions(
            'ROLE_FRONTEND_ADMINISTRATOR',
            CustomerUserAddress::class,
            [
                'EDIT' => $level,
            ]
        );

        $this->ajaxRequest(
            Request::METHOD_POST,
            $this->getUrl($routeName, [
                'customer_user_id' => $this->getReference(LoadCustomerUser::CUSTOMER_USER)->getId(),
                'id' => $this->getReference('grzegorz.brzeczyszczykiewicz@example.com.address_1')->getId(),
            ])
        );

        self::assertResponseStatusCodeEquals($this->client->getResponse(), $statusCode);
    }

    private static function aclProvider(): array
    {
        return [
            ['oro_customer_frontend_address_validation_customer_user_address', AccessLevel::NONE_LEVEL, 403],
            ['oro_customer_frontend_address_validation_customer_user_address', AccessLevel::GLOBAL_LEVEL, 200],
        ];
    }
}
