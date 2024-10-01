<?php

namespace Oro\Bundle\CustomerBundle\Entity\Repository;

use Oro\Bundle\DataGridBundle\Entity\Repository\GridViewUserRepository as BaseGridViewUserRepository;

class GridViewUserRepository extends BaseGridViewUserRepository
{
    /**
     * @return string
     */
    #[\Override]
    protected function getUserFieldName()
    {
        return 'customerUser';
    }
}
