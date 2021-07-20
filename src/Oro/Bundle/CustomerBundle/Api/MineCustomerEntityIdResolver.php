<?php

namespace Oro\Bundle\CustomerBundle\Api;

use Oro\Bundle\ApiBundle\Request\EntityIdResolverInterface;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;

/**
 * Resolves "mine" identifier for Customer entity.
 * This identifier can be used to identify a customer the current authenticated customer user belongs to.
 */
class MineCustomerEntityIdResolver implements EntityIdResolverInterface
{
    /** @var TokenAccessorInterface */
    private $tokenAccessor;

    public function __construct(TokenAccessorInterface $tokenAccessor)
    {
        $this->tokenAccessor = $tokenAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return <<<MARKDOWN
**mine** can be used to identify a customer the current authenticated customer user belongs to.
MARKDOWN;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve()
    {
        $user = $this->tokenAccessor->getUser();
        if ($user instanceof CustomerUser) {
            $customer = $user->getCustomer();
            if (null !== $customer) {
                return $customer->getId();
            }
        }

        return null;
    }
}
