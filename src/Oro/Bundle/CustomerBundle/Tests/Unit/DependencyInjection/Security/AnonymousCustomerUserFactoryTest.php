<?php
declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Tests\Unit\DependencyInjection\Security;

use Oro\Bundle\CustomerBundle\DependencyInjection\Security\AnonymousCustomerUserFactory;
use Oro\Bundle\TestFrameworkBundle\Test\DependencyInjection\ExtensionTestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\IntegerNode;
use Symfony\Component\DependencyInjection\ChildDefinition;

class AnonymousCustomerUserFactoryTest extends ExtensionTestCase
{
    private AnonymousCustomerUserFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new AnonymousCustomerUserFactory();
    }

    public function testCreate(): void
    {
        $container = $this->getContainerMock();

        $this->factory->create(
            $container,
            'fake_id',
            ['update_latency' => 300],
            'fake_user_provider',
            'fake_default_entry_point'
        );

        $this->assertDefinitionsLoaded(
            [
                'oro_customer.authentication.provider.anonymous_customer_user.fake_id',
                'oro_customer.authentication.listener.anonymous_customer_user.fake_id',
            ]
        );
        self::assertInstanceOf(
            ChildDefinition::class,
            $this->actualDefinitions['oro_customer.authentication.provider.anonymous_customer_user.fake_id']
        );
        self::assertEquals(
            'oro_customer.authentication.provider.anonymous_customer_user',
            $this->actualDefinitions['oro_customer.authentication.provider.anonymous_customer_user.fake_id']
                ->getParent()
        );
        self::assertEquals(
            [
                'index_2' => 300,
            ],
            $this->actualDefinitions['oro_customer.authentication.provider.anonymous_customer_user.fake_id']
                ->getArguments()
        );

        self::assertInstanceOf(
            ChildDefinition::class,
            $this->actualDefinitions['oro_customer.authentication.listener.anonymous_customer_user.fake_id']
        );
        self::assertEquals(
            'oro_customer.authentication.listener.anonymous_customer_user',
            $this->actualDefinitions['oro_customer.authentication.listener.anonymous_customer_user.fake_id']
                ->getParent()
        );
    }

    public function testGetPosition(): void
    {
        self::assertEquals('remember_me', $this->factory->getPosition());
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
