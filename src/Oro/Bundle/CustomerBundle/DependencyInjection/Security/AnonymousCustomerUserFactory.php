<?php

namespace Oro\Bundle\CustomerBundle\DependencyInjection\Security;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AuthenticatorFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Configures Anonymous Customer User definitions
 */
class AnonymousCustomerUserFactory implements AuthenticatorFactoryInterface
{
    public function createAuthenticator(
        ContainerBuilder $container,
        string $firewallName,
        array $config,
        string $userProviderId
    ): string {
        $authenticatorId = 'oro_customer.anonymous_customer_user.authenticator.' . $firewallName;
        $container
            ->setDefinition(
                $authenticatorId,
                new ChildDefinition('oro_customer.anonymous_customer_user.authenticator')
            );

        return $authenticatorId;
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

    public function getPriority(): int
    {
        return -60;
    }
}
