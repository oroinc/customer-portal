<?php

namespace Oro\Bundle\CustomerBundle\Entity\Repository;

use Oro\Bundle\DataGridBundle\Entity\Repository\GridViewRepository as BaseGridViewRepository;

class GridViewRepository extends BaseGridViewRepository
{
    /**
     * {@inheritdoc}
     */
    protected function getOwnerFieldName()
    {
        return 'customerUserOwner';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserFieldName()
    {
        return 'customerUser';
    }
}
