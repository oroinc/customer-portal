<?php

namespace Oro\Bundle\CustomerBundle\DependencyInjection\Security;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Configures Anonymous Customer User definitions
 */
class AnonymousCustomerUserFactory implements SecurityFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(
        ContainerBuilder $container,
        string $id,
        array $config,
        string $userProviderId,
        ?string $defaultEntryPointId
    ): array {
        $providerId = 'oro_customer.authentication.provider.anonymous_customer_user.'.$id;
        $container
            ->setDefinition(
                $providerId,
                new ChildDefinition('oro_customer.authentication.provider.anonymous_customer_user')
            );

        $listenerId = 'oro_customer.authentication.listener.anonymous_customer_user.'.$id;
        $container->setDefinition(
            $listenerId,
            new ChildDefinition('oro_customer.authentication.listener.anonymous_customer_user')
        );

        return [$providerId, $listenerId, $defaultEntryPointId];
    }

    /**
     * {@inheritDoc}
     */
    public function getPosition(): string
    {
        return 'remember_me';
    }

    /**
     * {@inheritDoc}
     */
    public function getKey(): string
    {
        return 'anonymous_customer_user';
    }

    public function addConfiguration(NodeDefinition $builder): void
    {
        $builder
            ->children()
                ->integerNode('update_latency')
                    ->defaultValue('%oro_customer.anonymous_customer_user.update_latency%')
                    ->info('Latency in seconds to update lastVisit datetime of AnonymousCustomerUser, ' .
                        'this parameter prevent too many requests to the database for update lastVisit datetime.')
                ->end()
            ->end();
    }
}
