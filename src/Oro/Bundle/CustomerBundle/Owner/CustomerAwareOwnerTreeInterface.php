<?php

namespace Oro\Bundle\CustomerBundle\Owner;

use Oro\Bundle\SecurityBundle\Owner\OwnerTreeInterface;

/**
 * Interface for frontend tree providers which can return owner tree by a specific customer.
 */
interface CustomerAwareOwnerTreeInterface
{
    /**
     * @param object $businessUnit
     * @return OwnerTreeInterface
     */
    public function getTreeByBusinessUnit($businessUnit): OwnerTreeInterface;
}
