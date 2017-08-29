<?php

namespace Oro\Bundle\CustomerBundle\Manager;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessor;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginManager
{
    /**
     * @var TokenAccessor
     */
    private $tokenAccessor;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param TokenAccessor $tokenAccessor
     * @param ContainerInterface $container
     */
    public function __construct(TokenAccessor $tokenAccessor, ContainerInterface $container)
    {
        $this->tokenAccessor = $tokenAccessor;
        $this->container = $container;
    }

    /**
     * @param $firewallName
     * @param CustomerUser $customerUser
     */
    public function logInUser($firewallName, CustomerUser $customerUser)
    {
        if ($this->tokenAccessor->hasUser()) {
            return;
        }

        $token = $this->createToken($firewallName, $customerUser);

        $this->tokenAccessor->setToken($token);

        $request = $this->container->get('request');
        $event = new InteractiveLoginEvent($request, $token);
        $this->container->get('event_dispatcher')->dispatch('security.interactive_login', $event);
    }

    /**
     * @param string $firewall
     * @param CustomerUser $customerUser
     * @return UsernamePasswordToken
     *
     */
    private function createToken($firewall, CustomerUser $customerUser)
    {
        /** @var Organization $organization */
        $organization = $customerUser->getOrganization();

        return new UsernamePasswordOrganizationToken(
            $customerUser,
            null,
            $firewall,
            $organization,
            $customerUser->getRoles()
        );
    }
}
