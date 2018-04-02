<?php

namespace Oro\Bundle\CustomerBundle\Acl\Group;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\SecurityBundle\Acl\Group\AclGroupProviderInterface;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;

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

        return null === $user || $user instanceof CustomerUser;
    }

    /**
     * {@inheritDoc}
     */
    public function getGroup()
    {
        return CustomerUser::SECURITY_GROUP;
    }
}
