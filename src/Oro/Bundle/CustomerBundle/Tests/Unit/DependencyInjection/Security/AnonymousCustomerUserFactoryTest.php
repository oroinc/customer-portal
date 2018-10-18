<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\DependencyInjection\Security;

use Oro\Bundle\CustomerBundle\DependencyInjection\Security\AnonymousCustomerUserFactory;
use Oro\Bundle\TestFrameworkBundle\Test\DependencyInjection\ExtensionTestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\IntegerNode;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class AnonymousCustomerUserFactoryTest extends ExtensionTestCase
{
    /**
     * @var AnonymousCustomerUserFactory
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = new AnonymousCustomerUserFactory();
    }

    public function testCreate()
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
        $this->assertInstanceOf(
            ChildDefinition::class,
            $this->actualDefinitions['oro_customer.authentication.provider.anonymous_customer_user.fake_id']
        );
        $this->assertEquals(
            'oro_customer.authentication.provider.anonymous_customer_user',
            $this->actualDefinitions['oro_customer.authentication.provider.anonymous_customer_user.fake_id']
                ->getParent()
        );
        $this->assertEquals(
            [
                'index_2' => 300,
            ],
            $this->actualDefinitions['oro_customer.authentication.provider.anonymous_customer_user.fake_id']
                ->getArguments()
        );

        $this->assertInstanceOf(
            ChildDefinition::class,
            $this->actualDefinitions['oro_customer.authentication.listener.anonymous_customer_user.fake_id']
        );
        $this->assertEquals(
            'oro_customer.authentication.listener.anonymous_customer_user',
            $this->actualDefinitions['oro_customer.authentication.listener.anonymous_customer_user.fake_id']
                ->getParent()
        );
    }

    public function testGetPosition()
    {
        $this->assertEquals('remember_me', $this->factory->getPosition());
    }

    public function testGetKey()
    {
        $this->assertEquals('anonymous_customer_user', $this->factory->getKey());
    }

    public function testAddConfiguration()
    {
        $config = new ArrayNodeDefinition('root');
        $this->factory->addConfiguration($config);
        $loadedNodes = $config->getNode()->getChildren();
        $this->assertCount(1, $loadedNodes);
        $this->assertInstanceOf(IntegerNode::class, $loadedNodes['update_latency']);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getContainerMock()
    {
        $container = $this->createMock(ContainerBuilder::class);
        $container->expects($this->any())
            ->method('setDefinition')
            ->will(
                $this->returnCallback(
                    function ($id, Definition $definition) {
                        $this->actualDefinitions[$id] = $definition;

                        return $definition;
                    }
                )
            );

        return $container;
    }
}
