<?php

namespace Oro\Bundle\CustomerBundle\Acl\Resolver;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\User;

class RoleTranslationPrefixResolver
{
    const BACKEND_PREFIX = 'oro.customer.security.access-level.';
    const FRONTEND_PREFIX = 'oro.customer.frontend.security.access-level.';

    /** @var TokenAccessorInterface */
    protected $tokenAccessor;

    public function __construct(TokenAccessorInterface $tokenAccessor)
    {
        $this->tokenAccessor = $tokenAccessor;
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    public function getPrefix()
    {
        $user = $this->tokenAccessor->getUser();

        if ($user instanceof User) {
            return self::BACKEND_PREFIX;
        } elseif ($user instanceof CustomerUser) {
            return self::FRONTEND_PREFIX;
        }

        throw new \RuntimeException('This method must be called only for logged User or CustomerUser');
    }
}
