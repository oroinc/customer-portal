<?php

namespace Oro\Bundle\CustomerBundle\Datagrid\Extension;

use Oro\Bundle\DataGridBundle\Extension\GridViews\GridViewsExtension as BaseGridViewsExtension;

/**
 * Frontend-specific grid views extension that applies customer-specific permissions.
 *
 * This extension extends the base grid views functionality to enforce frontend-specific
 * permissions for viewing, creating, editing, deleting, and sharing grid views in the customer portal.
 */
class GridViewsExtension extends BaseGridViewsExtension
{
    #[\Override]
    protected function getPermissions()
    {
        return [
            'VIEW'        => $this->authorizationChecker->isGranted('oro_customer_frontend_gridview_view'),
            'CREATE'      => $this->authorizationChecker->isGranted('oro_customer_frontend_gridview_create'),
            'EDIT'        => $this->authorizationChecker->isGranted('oro_customer_frontend_gridview_update'),
            'DELETE'      => $this->authorizationChecker->isGranted('oro_customer_frontend_gridview_delete'),
            'SHARE'       => $this->authorizationChecker->isGranted('oro_customer_frontend_gridview_publish'),
            'EDIT_SHARED' => $this->authorizationChecker->isGranted('oro_customer_frontend_gridview_update_public')
        ];
    }
}
