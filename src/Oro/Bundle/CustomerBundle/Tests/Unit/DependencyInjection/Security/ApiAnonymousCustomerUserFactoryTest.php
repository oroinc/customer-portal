<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Tests\Unit\DependencyInjection\Security;

use Oro\Bundle\CustomerBundle\DependencyInjection\Security\ApiAnonymousCustomerUserFactory;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ApiAnonymousCustomerUserFactoryTest extends \PHPUnit\Framework\TestCase
{
    private ApiAnonymousCustomerUserFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new ApiAnonymousCustomerUserFactory();
    }

    public function testCreate(): void
    {
        $container = new ContainerBuilder();

        $this->factory->create(
            $container,
            'fake_id',
            ['update_latency' => 300],
            'fake_user_provider',
            'fake_default_entry_point'
        );

        self::assertEquals(
            new ChildDefinition('oro_customer.authentication.provider.api_anonymous_customer_user'),
            $container->getDefinition('oro_customer.authentication.provider.api_anonymous_customer_user.fake_id')
        );
        self::assertEquals(
            new ChildDefinition('oro_customer.authentication.listener.api_anonymous_customer_user'),
            $container->getDefinition('oro_customer.authentication.listener.api_anonymous_customer_user.fake_id')
        );
    }

    public function testGetPosition(): void
    {
        self::assertEquals('remember_me', $this->factory->getPosition());
    }

    public function testGetKey(): void
    {
        self::assertEquals('api_anonymous_customer_user', $this->factory->getKey());
    }
}
