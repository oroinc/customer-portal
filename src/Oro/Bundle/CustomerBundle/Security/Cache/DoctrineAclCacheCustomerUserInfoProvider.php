<?php

namespace Oro\Bundle\CustomerBundle\Security\Cache;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\SecurityBundle\Cache\DoctrineAclCacheUserInfoProviderInterface;

/**
 * Doctrine ACL query cache info provider that adds support of tokens with Customer user as user.
 */
class DoctrineAclCacheCustomerUserInfoProvider implements DoctrineAclCacheUserInfoProviderInterface
{
    private TokenAccessorInterface $tokenAccessor;
    private DoctrineAclCacheUserInfoProviderInterface $innerProvider;

    public function __construct(
        TokenAccessorInterface $tokenAccessor,
        DoctrineAclCacheUserInfoProviderInterface $innerProvider
    ) {
        $this->tokenAccessor = $tokenAccessor;
        $this->innerProvider = $innerProvider;
    }

    #[\Override]
    public function getCurrentUserCacheKeyInfo(): array
    {
        if ($this->tokenAccessor->hasUser() && $this->tokenAccessor->getUser() instanceof CustomerUser) {
            return [
                Customer::class,
                $this->tokenAccessor->getUser()->getCustomer()->getId()
            ];
        }

        return $this->innerProvider->getCurrentUserCacheKeyInfo();
    }
}
