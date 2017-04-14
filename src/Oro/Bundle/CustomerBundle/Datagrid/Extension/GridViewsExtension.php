<?php

namespace Oro\Bundle\CustomerBundle\Datagrid\Extension;

use Oro\Bundle\DataGridBundle\Extension\GridViews\GridViewsExtension as BaseGridViewsExtension;

class GridViewsExtension extends BaseGridViewsExtension
{
    /**
     * {@inheritdoc}
     */
    protected function getPermissions()
    {
        return [
            'VIEW' => $this->securityFacade->isGranted('oro_customer_frontend_gridview_view'),
            'CREATE' => $this->securityFacade->isGranted('oro_customer_frontend_gridview_create'),
            'EDIT' => $this->securityFacade->isGranted('oro_customer_frontend_gridview_update'),
            'DELETE' => $this->securityFacade->isGranted('oro_customer_frontend_gridview_delete'),
            'SHARE' => $this->securityFacade->isGranted('oro_customer_frontend_gridview_publish'),
            'EDIT_SHARED' => $this->securityFacade->isGranted('oro_customer_frontend_gridview_update_public')
        ];
    }
}
