<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\ImportExport\TemplateFixture;

use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\ImportExport\TemplateFixture\CustomerUserAddressFixture;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateEntityRegistry;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateManager;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerUserAddressFixtureTest extends TestCase
{
    use EntityTrait;

    private TemplateEntityRegistry&MockObject $templateEntityRegistry;
    private CustomerUserAddressFixture $fixture;

    #[\Override]
    protected function setUp(): void
    {
        $this->templateEntityRegistry = $this->createMock(TemplateEntityRegistry::class);

        $templateManager = $this->createMock(TemplateManager::class);
        $templateManager->expects(self::any())
            ->method('getEntityRegistry')
            ->willReturn($this->templateEntityRegistry);

        $this->fixture = new CustomerUserAddressFixture();
        $this->fixture->setTemplateManager($templateManager);
    }

    public function testGetEntityClass(): void
    {
        self::assertEquals(CustomerUserAddress::class, $this->fixture->getEntityClass());
    }

    public function testFillEntityData(): void
    {
        $entity = new CustomerUserAddress();
        $this->fixture->fillEntityData('test', $entity);

        $expected = $this->getEntity(
            CustomerUserAddress::class,
            [
                'frontendOwner' => $this->getEntity(
                    CustomerUser::class,
                    [
                        'id' => 2,
                        'email' => 'customer_user@example.com',
                        'salt' => $entity->getFrontendOwner()->getSalt()
                    ]
                ),
                'types' => [new AddressType('billing'), new AddressType('shipping')],
                'defaults' => [new AddressType('billing'), new AddressType('shipping')],
                'phone' => '(+1) 212 123 4567',
                'owner' => $this->getEntity(
                    User::class,
                    ['id' => 1, 'salt' => $entity->getOwner()->getSalt(), 'username' => 'admin_user']
                ),
                'primary' => true,
                'label' => 'Headquarters',
                'street' => '23400 Caldwell Road',
                'city' => 'Rochester',
                'postalCode' => '14608',
                'country' => new Country('US'),
                'region' => new Region('US-NY'),
                'organization' => 'Company A',
                'namePrefix' => 'Mr.',
                'firstName' => 'John',
                'lastName' => 'Doe',
                'nameSuffix' => 'Jr.',
                'validatedAt' => new \DateTime('now', new \DateTimeZone('UTC')),
            ]
        );

        self::assertEquals(
            $expected->getValidatedAt()->format('Y-m-d H:i:s'),
            $entity->getValidatedAt()->format('Y-m-d H:i:s')
        );

        $expected->setValidatedAt(null);
        $entity->setValidatedAt(null);

        self::assertEquals($expected, $entity);
    }

    public function testGetData(): void
    {
        $key = 'Example of Customer User Address';

        $this->templateEntityRegistry->expects(self::once())
            ->method('hasEntity')
            ->with(CustomerUserAddress::class, $key)
            ->willReturn(false);

        $entity = null;

        $this->templateEntityRegistry->expects(self::once())
            ->method('addEntity')
            ->with(CustomerUserAddress::class, $key, self::isInstanceOf(CustomerUserAddress::class))
            ->willReturnCallback(
                function (string $entityClass, string $entityKey, CustomerUserAddress $address) use (&$entity) {
                    $entity = $address;
                }
            );

        $this->templateEntityRegistry->expects(self::once())
            ->method('getData')
            ->with(self::isInstanceOf(TemplateManager::class), CustomerUserAddress::class, $key)
            ->willReturnCallback(function () use (&$entity) {
                return [$entity];
            });
        $actual = $this->fixture->getData();

        self::assertEquals([$this->getEntity(CustomerUserAddress::class, ['id' => 1])], $actual);
    }
}
