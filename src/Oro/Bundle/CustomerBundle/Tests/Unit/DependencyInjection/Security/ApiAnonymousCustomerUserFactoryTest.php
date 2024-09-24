<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Tests\Unit\DependencyInjection\Security;

use Oro\Bundle\CustomerBundle\DependencyInjection\Security\ApiAnonymousCustomerUserFactory;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ApiAnonymousCustomerUserFactoryTest extends \PHPUnit\Framework\TestCase
{
    private ApiAnonymousCustomerUserFactory $factory;

    #[\Override]
    protected function setUp(): void
    {
        $this->factory = new ApiAnonymousCustomerUserFactory();
    }

    public function testCreate(): void
    {
        $container = new ContainerBuilder();

        $this->factory->createAuthenticator(
            $container,
            'fake_id',
            ['update_latency' => 300],
            'fake_user_provider',
        );

        self::assertEquals(
            new ChildDefinition('oro_customer.api_anonymous_customer_user.authenticator'),
            $container->getDefinition('oro_customer.api_anonymous_customer_user.authenticator.fake_id')
        );
    }

    public function testGetPosition(): void
    {
        self::assertEquals(-60, $this->factory->getPriority());
    }

    public function testGetKey(): void
    {
        self::assertEquals('api_anonymous_customer_user', $this->factory->getKey());
    }
}
