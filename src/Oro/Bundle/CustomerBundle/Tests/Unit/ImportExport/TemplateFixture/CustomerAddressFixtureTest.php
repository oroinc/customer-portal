<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\ImportExport\TemplateFixture;

use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\ImportExport\TemplateFixture\CustomerAddressFixture;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateEntityRegistry;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateManager;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\Testing\Unit\EntityTrait;

class CustomerAddressFixtureTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var TemplateEntityRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $templateEntityRegistry;

    /** @var CustomerAddressFixture */
    private $fixture;

    protected function setUp(): void
    {
        $this->templateEntityRegistry = $this->createMock(TemplateEntityRegistry::class);

        /** @var TemplateManager|\PHPUnit\Framework\MockObject\MockObject $templateManager */
        $templateManager = $this->createMock(TemplateManager::class);
        $templateManager->expects($this->any())
            ->method('getEntityRegistry')
            ->willReturn($this->templateEntityRegistry);

        $this->fixture = new CustomerAddressFixture();
        $this->fixture->setTemplateManager($templateManager);
    }

    public function testGetEntityClass(): void
    {
        $this->assertEquals(CustomerAddress::class, $this->fixture->getEntityClass());
    }

    public function testFillEntityData(): void
    {
        $entity = new CustomerAddress();

        $this->fixture->fillEntityData('test', $entity);

        $this->assertEquals(
            $this->getEntity(
                CustomerAddress::class,
                [
                    'frontendOwner' => $this->getEntity(
                        Customer::class,
                        ['id' => 1, 'name' => 'Company A - East Division']
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
        $key = 'Example of Customer Address';

        $this->templateEntityRegistry->expects($this->once())
            ->method('hasEntity')
            ->with(CustomerAddress::class, $key)
            ->willReturn(false);

        $entity = null;

        $this->templateEntityRegistry->expects($this->once())
            ->method('addEntity')
            ->with(CustomerAddress::class, $key, $this->isInstanceOf(CustomerAddress::class))
            ->willReturnCallback(
                function (string $entityClass, string $entityKey, CustomerAddress $address) use (&$entity) {
                    $entity = $address;
                }
            );

        $this->templateEntityRegistry->expects($this->once())
            ->method('getData')
            ->with($this->isInstanceOf(TemplateManager::class), CustomerAddress::class, $key)
            ->willReturnCallback(
                function () use (&$entity) {
                    return $entity;
                }
            );
        $actual = $this->fixture->getData();

        $this->assertEquals($this->getEntity(CustomerAddress::class, ['id' => 1]), $actual);
    }
}
