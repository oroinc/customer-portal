<?php

namespace Oro\Bundle\CustomerBundle\Acl\Group;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\SecurityBundle\Acl\Group\AclGroupProviderInterface;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;

/**
 * Detect ACL group for commerce.
 */
class AclGroupProvider implements AclGroupProviderInterface
{
    /** @var TokenAccessorInterface */
    private $tokenAccessor;

    /**
     * @param TokenAccessorInterface $tokenAccessor
     */
    public function __construct(TokenAccessorInterface $tokenAccessor)
    {
        $this->tokenAccessor = $tokenAccessor;
    }

    /**
     * {@inheritDoc}
     */
    public function supports()
    {
        $user = $this->tokenAccessor->getUser();
        $token = $this->tokenAccessor->getToken();

        return $token instanceof AnonymousCustomerUserToken || $user instanceof CustomerUser;
    }

    /**
     * {@inheritDoc}
     */
    public function getGroup()
    {
        return CustomerUser::SECURITY_GROUP;
    }
}
