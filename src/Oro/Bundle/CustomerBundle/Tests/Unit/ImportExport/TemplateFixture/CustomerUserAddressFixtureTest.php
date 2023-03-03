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

class CustomerUserAddressFixtureTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var TemplateEntityRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $templateEntityRegistry;

    /** @var CustomerUserAddressFixture */
    private $fixture;

    protected function setUp(): void
    {
        $this->templateEntityRegistry = $this->createMock(TemplateEntityRegistry::class);

        /** @var TemplateManager|\PHPUnit\Framework\MockObject\MockObject $templateManager */
        $templateManager = $this->createMock(TemplateManager::class);
        $templateManager->expects($this->any())
            ->method('getEntityRegistry')
            ->willReturn($this->templateEntityRegistry);

        $this->fixture = new CustomerUserAddressFixture();
        $this->fixture->setTemplateManager($templateManager);
    }

    public function testGetEntityClass(): void
    {
        $this->assertEquals(CustomerUserAddress::class, $this->fixture->getEntityClass());
    }

    public function testFillEntityData(): void
    {
        $entity = new CustomerUserAddress();
        $this->fixture->fillEntityData('test', $entity);

        $this->assertEquals(
            $this->getEntity(
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
                ]
            ),
            $entity
        );
    }

    public function testGetData(): void
    {
        $key = 'Example of Customer User Address';

        $this->templateEntityRegistry->expects($this->once())
            ->method('hasEntity')
            ->with(CustomerUserAddress::class, $key)
            ->willReturn(false);

        $entity = null;

        $this->templateEntityRegistry->expects($this->once())
            ->method('addEntity')
            ->with(CustomerUserAddress::class, $key, $this->isInstanceOf(CustomerUserAddress::class))
            ->willReturnCallback(
                function (string $entityClass, string $entityKey, CustomerUserAddress $address) use (&$entity) {
                    $entity = $address;
                }
            );

        $this->templateEntityRegistry->expects($this->once())
            ->method('getData')
            ->with($this->isInstanceOf(TemplateManager::class), CustomerUserAddress::class, $key)
            ->willReturnCallback(
                function () use (&$entity) {
                    return $entity;
                }
            );
        $actual = $this->fixture->getData();

        $this->assertEquals($this->getEntity(CustomerUserAddress::class, ['id' => 1]), $actual);
    }
}
