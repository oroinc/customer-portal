<?php

namespace Oro\Bundle\CustomerBundle\Entity\Repository;

use Oro\Bundle\DataGridBundle\Entity\Repository\GridViewUserRepository as BaseGridViewUserRepository;

/**
 * Repository for customer user grid view user associations with frontend-specific field mappings.
 *
 * This repository extends the base grid view user repository to use the customer user field name
 * for user relationships in grid view user queries.
 */
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
