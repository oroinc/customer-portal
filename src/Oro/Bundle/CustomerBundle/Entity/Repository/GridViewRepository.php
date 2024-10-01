<?php

namespace Oro\Bundle\CustomerBundle\Entity\Repository;

use Oro\Bundle\DataGridBundle\Entity\Repository\GridViewRepository as BaseGridViewRepository;

class GridViewRepository extends BaseGridViewRepository
{
    #[\Override]
    protected function getOwnerFieldName()
    {
        return 'customerUserOwner';
    }

    #[\Override]
    protected function getUserFieldName()
    {
        return 'customerUser';
    }
}
