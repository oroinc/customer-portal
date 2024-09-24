<?php

namespace Oro\Bundle\CustomerBundle\DependencyInjection\Security;

use Oro\Bundle\SecurityBundle\Authentication\Listener\OnNoTokenAccessListener;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AuthenticatorFactoryInterface;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\FirewallListenerFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Configures definitions for anonymous customer user that is not stored in the database.
 */
class ApiAnonymousCustomerUserFactory implements AuthenticatorFactoryInterface, FirewallListenerFactoryInterface
{
    #[\Override]
    public function createAuthenticator(
        ContainerBuilder $container,
        string $firewallName,
        array $config,
        string $userProviderId
    ): string {
        $authenticatorId = 'oro_customer.api_anonymous_customer_user.authenticator.' . $firewallName;
        $container
            ->setDefinition(
                $authenticatorId,
                new ChildDefinition('oro_customer.api_anonymous_customer_user.authenticator')
            );

        return $authenticatorId;
    }

    #[\Override]
    public function getPriority(): int
    {
        return -60;
    }

    #[\Override]
    public function getKey(): string
    {
        return 'api_anonymous_customer_user';
    }

    #[\Override]
    public function addConfiguration(NodeDefinition $builder): void
    {
    }

    #[\Override]
    public function createListeners(ContainerBuilder $container, string $firewallName, array $config): array
    {
        $onNoTokenListenerId = 'oro_customer.authentication.listener.notoken.' . $firewallName;
        $container
            ->register($onNoTokenListenerId, OnNoTokenAccessListener::class)
            ->setArguments([
                new Reference('security.token_storage'),
            ]);

        return [$onNoTokenListenerId];
    }
}
