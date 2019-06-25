<?php

namespace Oro\Bundle\CustomerBundle\Acl\AccessRule;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\SecurityBundle\AccessRule\AccessRuleOptionMatcherInterface;
use Oro\Bundle\SecurityBundle\AccessRule\Criteria;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * A class that checks whether access rules are applicable for a criteria object.
 * The access rule option matcher that supports the following options:
 * * frontend - whether the current security context represents a storefront request
 */
class FrontendAccessRuleOptionMatcher implements AccessRuleOptionMatcherInterface
{
    private const OPTION_FRONTEND = 'frontend';

    /** @var AccessRuleOptionMatcherInterface */
    private $innerMatcher;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * @param AccessRuleOptionMatcherInterface $innerMatcher
     * @param TokenStorageInterface            $tokenStorage
     */
    public function __construct(
        AccessRuleOptionMatcherInterface $innerMatcher,
        TokenStorageInterface $tokenStorage
    ) {
        $this->innerMatcher = $innerMatcher;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function matches(Criteria $criteria, string $optionName, $optionValue): bool
    {
        if (self::OPTION_FRONTEND === $optionName) {
            $isFrontend = $this->isFrontend();

            return
                (true === $optionValue && $isFrontend)
                || (false === $optionValue && !$isFrontend);
        }

        return $this->innerMatcher->matches($criteria, $optionName, $optionValue);
    }

    /**
     * @return bool
     */
    private function isFrontend(): bool
    {
        $token = $this->tokenStorage->getToken();

        return
            null !== $token
            && (
                $token instanceof AnonymousCustomerUserToken
                || $token->getUser() instanceof CustomerUser
            );
    }
}
