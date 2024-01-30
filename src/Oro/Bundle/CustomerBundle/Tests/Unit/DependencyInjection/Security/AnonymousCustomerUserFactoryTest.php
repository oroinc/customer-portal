<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Tests\Unit\DependencyInjection\Security;

use Oro\Bundle\CustomerBundle\DependencyInjection\Security\AnonymousCustomerUserFactory;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\IntegerNode;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AnonymousCustomerUserFactoryTest extends \PHPUnit\Framework\TestCase
{
    private AnonymousCustomerUserFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new AnonymousCustomerUserFactory();
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
            new ChildDefinition('oro_customer.anonymous_customer_user.authenticator'),
            $container->getDefinition('oro_customer.anonymous_customer_user.authenticator.fake_id')
        );
    }

    public function testGetPosition(): void
    {
        self::assertEquals(-60, $this->factory->getPriority());
    }

    public function testGetKey(): void
    {
        self::assertEquals('anonymous_customer_user', $this->factory->getKey());
    }

    public function testAddConfiguration(): void
    {
        $config = new ArrayNodeDefinition('root');

        $this->factory->addConfiguration($config);

        $loadedNodes = $config->getNode()->getChildren();
        self::assertCount(1, $loadedNodes);
        self::assertInstanceOf(IntegerNode::class, $loadedNodes['update_latency']);
    }
}
