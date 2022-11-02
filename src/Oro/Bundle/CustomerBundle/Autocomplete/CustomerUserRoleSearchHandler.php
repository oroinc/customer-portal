<?php

namespace Oro\Bundle\CustomerBundle\Autocomplete;

use Oro\Bundle\FormBundle\Autocomplete\SearchHandler;
use Oro\Component\DoctrineUtils\ORM\QueryBuilderUtil;

/**
 * Adds restriction to search query to get only predefined customer user roles
 */
class CustomerUserRoleSearchHandler extends SearchHandler
{
    /**
     * {@inheritdoc}
     */
    protected function getEntitiesByIds(array $entityIds)
    {
        $entityIds = array_filter(
            $entityIds,
            function ($id) {
                return $id !== null && $id !== '';
            }
        );
        if ($entityIds) {
            $queryBuilder = $this->entityRepository->createQueryBuilder('e');
            $queryBuilder->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->in(QueryBuilderUtil::getField('e', $this->idFieldName), ':entityIds'),
                    $queryBuilder->expr()->isNull('e.customer')
                )
            );
            $queryBuilder->setParameter('entityIds', $entityIds);

            return $queryBuilder->getQuery()->getResult();
        }

        return [];
    }
}
