<?php

namespace Oro\Bundle\CustomerBundle\Api;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\SecurityBundle\Acl\Extension\EntityAclExtension;
use Oro\Bundle\SecurityBundle\Acl\Extension\ObjectIdentityHelper;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Component\ChainProcessor\ContextInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Check whether the customer user is the profile owner.
 */
class CustomerUserProfileResolver
{
    public const ACL_RESOURCE = 'oro_customer_frontend_update_own_profile';

    private TokenAccessorInterface $tokenAccessor;
    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(
        TokenAccessorInterface $tokenAccessor,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->tokenAccessor = $tokenAccessor;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function hasProfilePermission(ContextInterface $context, int $customerUserId): bool
    {
        if ($context->getClassName() !== CustomerUser::class) {
            return false;
        }

        /** @var CustomerUser $customerUser */
        $currentCustomerUser = $this->tokenAccessor->getUser();
        $customerUserPermission = $this->authorizationChecker->isGranted(
            'EDIT',
            ObjectIdentityHelper::encodeIdentityString(EntityAclExtension::NAME, CustomerUser::class)
        );

        return
            $currentCustomerUser
            && $currentCustomerUser->getId() === $customerUserId
            && $this->authorizationChecker->isGranted(self::ACL_RESOURCE)
            && false === $customerUserPermission;
    }
}
