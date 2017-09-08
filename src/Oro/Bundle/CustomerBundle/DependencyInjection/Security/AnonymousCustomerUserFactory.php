<?php

namespace Oro\Bundle\CustomerBundle\DependencyInjection\Security;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class AnonymousCustomerUserFactory implements SecurityFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'oro_customer.authentication.provider.anonymous_customer_user.'.$id;
        $container
            ->setDefinition(
                $providerId,
                new DefinitionDecorator('oro_customer.authentication.provider.anonymous_customer_user')
            )
            ->replaceArgument(1, $config['update_latency']);

        $listenerId = 'oro_customer.authentication.listener.anonymous_customer_user.'.$id;
        $container->setDefinition(
            $listenerId,
            new DefinitionDecorator('oro_customer.authentication.listener.anonymous_customer_user')
        )->replaceArgument(3, $config['lifetime']);

        return [$providerId, $listenerId, $defaultEntryPoint];
    }

    /**
     * {@inheritDoc}
     */
    public function getPosition()
    {
        return 'remember_me';
    }

    /**
     * {@inheritDoc}
     */
    public function getKey()
    {
        return 'anonymous_customer_user';
    }

    /**
     * @param NodeDefinition $builder
     */
    public function addConfiguration(NodeDefinition $builder)
    {
        $builder
            ->children()
                ->integerNode('lifetime')
                    ->defaultValue('%oro_customer.anonymous_customer_user.lifetime%')
                    ->info('How long to store cookie, in seconds.')
                    ->end()
                ->integerNode('update_latency')
                    ->defaultValue('%oro_customer.anonymous_customer_user.update_latency%')
                    ->info('Latency in seconds to update lastVisit datetime of AnonymousCustomerUser, ' .
                        'this parameter prevent too many requests to the database for update lastVisit datetime.')
                ->end()
            ->end();
    }
}
