<?php

namespace Oro\Bundle\CustomerBundle\EventListener\Datagrid;

use Oro\Bundle\CustomerBundle\Acl\AccessRule\SelfManagedPublicCustomerUserRoleAccessRule;
use Oro\Bundle\DataGridBundle\Event\OrmResultBefore;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

/**
 * The listener for "frontend-customer-customer-user-roles-grid" datagrid that adds ACL checks.
 */
class CustomerUserRoleDatagridListener
{
    /**
     * @var AclHelper
     */
    protected $aclHelper;

    /**
     * @param AclHelper $aclHelper
     */
    public function __construct(AclHelper $aclHelper)
    {
        $this->aclHelper = $aclHelper;
    }

    /**
     * @param OrmResultBefore $event
     */
    public function onResultBefore(OrmResultBefore $event)
    {
        $this->aclHelper->apply(
            $event->getQuery(),
            'VIEW',
            [SelfManagedPublicCustomerUserRoleAccessRule::ENABLE_RULE => true]
        );
    }
}
