<?php

namespace Oro\Bundle\FrontendBundle\Datagrid\Extension;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\TagBundle\Grid\TagsExtension as BaseTagsExtension;

/**
 * Deactivates tags feature if datagrid displayed in storefront.
 */
class TagsExtension extends BaseTagsExtension
{
    private FrontendHelper $frontendHelper;

    public function setFrontendHelper(FrontendHelper $frontendHelper): void
    {
        $this->frontendHelper = $frontendHelper;
    }

    public function isApplicable(DatagridConfiguration $config)
    {
        if ($this->frontendHelper->isFrontendRequest()) {
            return false;
        }

        return parent::isApplicable($config);
    }
}
