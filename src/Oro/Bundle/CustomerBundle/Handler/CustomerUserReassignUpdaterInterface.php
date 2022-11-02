<?php

namespace Oro\Bundle\CustomerBundle\Handler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;

/**
 * This interface should be implemented by classes that handle customer user reassign.
 */
interface CustomerUserReassignUpdaterInterface
{
    public function update(CustomerUser $customerUser);

    /**
     * @param CustomerUser $customerUser
     * @return string[]
     */
    public function getClassNamesToUpdate(CustomerUser $customerUser): array;
}
