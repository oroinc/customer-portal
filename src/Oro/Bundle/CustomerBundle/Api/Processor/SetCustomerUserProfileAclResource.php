<?php

namespace Oro\Bundle\CustomerBundle\Api\Processor;

use Oro\Bundle\ApiBundle\Processor\SingleItemContext;
use Oro\Bundle\CustomerBundle\Api\CustomerUserProfileResolver;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Sets the customer user profile update permissions.
 * Allows the customer user to update profile data despite restricting editing of other customer users.
 *
 * Customer user profile requires additional permissions that will have higher priority
 * than the permissions that are available from customer user.
 * Therefore, should use the permission 'oro_customer_frontend_update_own_profile'.
 * This processor changes the  customer user permissions with profile permissions,
 * allowing to update the customer user profile independently of other permissions.
 */
class SetCustomerUserProfileAclResource implements ProcessorInterface
{
    private CustomerUserProfileResolver $customerUserProfileResolver;

    public function __construct(CustomerUserProfileResolver $customerUserProfileResolver)
    {
        $this->customerUserProfileResolver = $customerUserProfileResolver;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ContextInterface $context): void
    {
        /** @var SingleItemContext $context */

        if (null === $context->getId()) {
            return;
        }

        if ($this->customerUserProfileResolver->hasProfilePermission($context, $context->getId())) {
            // Set data security check resource. Current resource has a higher priority than other security permission.
            $context->getConfig()->setAclResource(CustomerUserProfileResolver::ACL_RESOURCE);
        }
    }
}
