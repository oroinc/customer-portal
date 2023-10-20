<?php

namespace Oro\Bundle\CustomerBundle\DependencyInjection\Security;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Configures definitions for anonymous customer user that is not stored in the database.
 */
class ApiAnonymousCustomerUserFactory implements SecurityFactoryInterface
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
        $baseProviderId = 'oro_customer.authentication.provider.api_anonymous_customer_user';
        $providerId = $baseProviderId . '.' . $id;
        $container->setDefinition($providerId, new ChildDefinition($baseProviderId));

        $baseListenerId = 'oro_customer.authentication.listener.api_anonymous_customer_user';
        $listenerId = $baseListenerId . '.' . $id;
        $container->setDefinition($listenerId, new ChildDefinition($baseListenerId));

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
        return 'api_anonymous_customer_user';
    }

    public function addConfiguration(NodeDefinition $builder): void
    {
    }
}
