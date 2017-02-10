<?php

namespace Oro\Bundle\CommerceMenuBundle\Menu\ContextProvider;

use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;

interface CustomerGroupMenuContextProviderInterface
{
    /**
     * @param CustomerGroup $customerGroup
     * @return array
     */
    public function getContexts(CustomerGroup $customerGroup);
}
