<?php

namespace Oro\Bundle\CustomerBundle\Entity\Repository;

use Oro\Bundle\DataGridBundle\Entity\Repository\GridViewRepository as BaseGridViewRepository;

/**
 * Repository for customer user grid views with frontend-specific field mappings.
 *
 * This repository extends the base grid view repository to use customer user-specific field names
 * for owner and user relationships in grid view queries.
 */
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
