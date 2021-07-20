<?php

namespace Oro\Bundle\CustomerBundle\Acl\Group;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\SecurityBundle\Acl\Group\AclGroupProviderInterface;

/**
 * Detects ACL group for the storefront.
 */
class AclGroupProvider implements AclGroupProviderInterface
{
    /** @var FrontendHelper */
    private $frontendHelper;

    public function __construct(FrontendHelper $frontendHelper)
    {
        $this->frontendHelper = $frontendHelper;
    }

    /**
     * {@inheritDoc}
     */
    public function supports()
    {
        return $this->frontendHelper->isFrontendRequest();
    }

    /**
     * {@inheritDoc}
     */
    public function getGroup()
    {
        return CustomerUser::SECURITY_GROUP;
    }
}
