<?php

namespace Oro\Bundle\CustomerBundle\Security;

use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver as BaseAuthenticationTrustResolver;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Make AuthenticationTrustResolver behave the same as Anonymous token
 *
 * Extends the original AuthenticationTrustResolver because the
 * ACL security bundle depends on that class instead of using the interface.
 */
class AuthenticationTrustResolver extends BaseAuthenticationTrustResolver
{
    /**
     * @var AuthenticationTrustResolverInterface
     */
    private $decoratedResolver;

    public function __construct(AuthenticationTrustResolverInterface $decoratedResolver)
    {
        $this->decoratedResolver = $decoratedResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function isAnonymous(TokenInterface $token = null)
    {
        return $this->isAnonymousCustomerUser($token) || $this->decoratedResolver->isAnonymous($token);
    }

    /**
     * {@inheritdoc}
     */
    public function isRememberMe(TokenInterface $token = null)
    {
        return $this->decoratedResolver->isRememberMe($token);
    }

    /**
     * {@inheritdoc}
     */
    public function isFullFledged(TokenInterface $token = null)
    {
        return !$this->isAnonymousCustomerUser($token) && $this->decoratedResolver->isFullFledged($token);
    }

    /**
     * @param TokenInterface|null $token
     *
     * @return bool
     */
    private function isAnonymousCustomerUser(TokenInterface $token = null)
    {
        return $token instanceof AnonymousCustomerUserToken;
    }
}
