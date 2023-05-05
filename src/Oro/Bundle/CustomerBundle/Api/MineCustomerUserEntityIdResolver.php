<?php

namespace Oro\Bundle\CustomerBundle\Api;

use Oro\Bundle\ApiBundle\Request\EntityIdResolverInterface;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;

/**
 * Resolves "mine" identifier for CustomerUser entity.
 * This identifier can be used to identify the current authenticated customer user.
 */
class MineCustomerUserEntityIdResolver implements EntityIdResolverInterface
{
    private TokenAccessorInterface $tokenAccessor;

    public function __construct(TokenAccessorInterface $tokenAccessor)
    {
        $this->tokenAccessor = $tokenAccessor;
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string
    {
        return <<<MARKDOWN
**mine** can be used to identify the current authenticated customer user.
MARKDOWN;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(): mixed
    {
        $user = $this->tokenAccessor->getUser();

        return $user instanceof CustomerUser
            ? $user->getId()
            : null;
    }
}
