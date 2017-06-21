<?php

namespace Oro\Bundle\CustomerBundle\Placeholder;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;

class PlaceholderFilter
{
    /** @var TokenAccessorInterface */
    protected $tokenAccessor;

    /**
     * @param TokenAccessorInterface $tokenAccessor
     */
    public function __construct(TokenAccessorInterface $tokenAccessor)
    {
        $this->tokenAccessor = $tokenAccessor;
    }

    /**
     * @return bool
     */
    public function isUserApplicable()
    {
        return $this->tokenAccessor->getUser() instanceof CustomerUser;
    }

    /**
     * @return bool
     */
    public function isLoginRequired()
    {
        return !is_object($this->tokenAccessor->getUser());
    }

    /**
     * @return bool
     */
    public function isFrontendApplicable()
    {
        $user = $this->tokenAccessor->getUser();

        return !is_object($user) || $user instanceof CustomerUser;
    }
}
