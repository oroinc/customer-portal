<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Provider;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Provider\CustomerEntityNameProvider;
use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;
use PHPUnit\Framework\TestCase;

class CustomerEntityNameProviderTest extends TestCase
{
    /**
     * @var CustomerEntityNameProvider
     */
    private $provider;

    protected function setUp()
    {
        $this->provider = new CustomerEntityNameProvider();
    }

    public function testGetNameForShortFormat(): void
    {
        $this->assertFalse(
            $this->provider->getName(
                EntityNameProviderInterface::SHORT,
                null,
                new Customer()
            )
        );

        $this->assertFalse(
            $this->provider->getName(null, null, new Customer())
        );
    }

    public function testGetNameForUnsupportedEntity(): void
    {
        $this->assertFalse(
            $this->provider->getName(
                EntityNameProviderInterface::FULL,
                null,
                new \stdClass()
            )
        );
    }

    public function testGetName(): void
    {
        $customer = new Customer();
        $customer->setName('default name');

        $this->assertEquals(
            'default name',
            $this->provider->getName(EntityNameProviderInterface::FULL, null, $customer)
        );
    }

    public function testGetNameDQL(): void
    {
        $this->assertFalse(
            $this->provider->getNameDQL(
                EntityNameProviderInterface::FULL,
                null,
                Customer::class,
                'customer'
            )
        );
    }
}
