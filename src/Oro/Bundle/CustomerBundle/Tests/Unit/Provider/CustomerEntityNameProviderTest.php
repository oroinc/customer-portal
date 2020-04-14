<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Provider;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Provider\CustomerEntityNameProvider;
use Oro\Bundle\EntityBundle\Provider\EntityNameProvider;
use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;

class CustomerEntityNameProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var CustomerEntityNameProvider */
    private $provider;

    /** @var EntityNameProvider */
    private $defaultEntityNameProvider;

    protected function setUp(): void
    {
        $this->defaultEntityNameProvider = $this->createMock(EntityNameProvider::class);

        $this->provider = new CustomerEntityNameProvider($this->defaultEntityNameProvider);
    }

    public function testGetName(): void
    {
        $locale = null;
        $entity = new Customer();

        $this->defaultEntityNameProvider->expects($this->exactly(2))
            ->method('getName')
            ->with(
                EntityNameProviderInterface::SHORT,
                $locale,
                $entity
            )
            ->willReturn('Customer Short Name');

        $this->assertEquals(
            'Customer Short Name',
            $this->provider->getName(EntityNameProviderInterface::FULL, $locale, $entity)
        );

        $this->assertEquals(
            'Customer Short Name',
            $this->provider->getName(EntityNameProviderInterface::SHORT, $locale, $entity)
        );
    }

    public function testGetNameForUnsupportedEntity(): void
    {
        $this->assertFalse(
            $this->provider->getName(EntityNameProviderInterface::FULL, null, new \stdClass())
        );
    }

    public function testGetNameDQL(): void
    {
        $locale = null;
        $className = Customer::class;
        $alias = 'customer';

        $this->defaultEntityNameProvider->expects($this->exactly(2))
            ->method('getNameDQL')
            ->with(
                EntityNameProviderInterface::SHORT,
                $locale,
                $className,
                $alias
            )
            ->willReturn('Customer Short Name DQL');

        $this->assertEquals(
            'Customer Short Name DQL',
            $this->provider->getNameDQL(EntityNameProviderInterface::FULL, $locale, $className, $alias)
        );

        $this->assertEquals(
            'Customer Short Name DQL',
            $this->provider->getNameDQL(EntityNameProviderInterface::SHORT, $locale, $className, $alias)
        );
    }

    public function testGetNameDQLForUnsupportedClass(): void
    {
        $this->assertFalse(
            $this->provider->getNameDQL(EntityNameProviderInterface::FULL, null, \stdClass::class, 'customer')
        );
    }
}
