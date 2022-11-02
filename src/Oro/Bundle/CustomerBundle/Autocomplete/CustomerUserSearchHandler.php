<?php

namespace Oro\Bundle\CustomerBundle\Autocomplete;

use Oro\Bundle\FormBundle\Autocomplete\SearchHandler as BaseSearchHandler;
use Oro\Bundle\SearchBundle\Query\Criteria\Criteria;
use Oro\Bundle\SearchBundle\Query\Query;
use Oro\Bundle\SearchBundle\Query\Result\Item;

/**
 * The autocomplete handler to search customer users.
 */
class CustomerUserSearchHandler extends BaseSearchHandler
{
    const DELIMITER = ';';

    /**
     * {@inheritdoc}
     */
    protected function searchEntities($search, $firstResult, $maxResults)
    {
        if (!str_contains($search, static::DELIMITER)) {
            return [];
        }

        [$searchTerm, $customerId] = explode(static::DELIMITER, $search, 2);
        $entityIds = $this->searchIdsByTermAndCustomer($searchTerm, $firstResult, $maxResults, $customerId);
        if (!count($entityIds)) {
            return [];
        }

        $queryBuilder = $this->entityRepository->createQueryBuilder('e');
        $queryBuilder
            ->where($queryBuilder->expr()->in('e.' . $this->idFieldName, ':entityIds'))
            ->addOrderBy($queryBuilder->expr()->asc('e.email'))
            ->setParameter('entityIds', $entityIds);

        if ($customerId) {
            $queryBuilder
                ->andWhere('e.customer = :customer')
                ->setParameter('customer', $customerId);
        }

        $query = $this->aclHelper->apply($queryBuilder);

        return $query->getResult();
    }

    /**
     * @param string $search
     * @param int $firstResult
     * @param int $maxResults
     * @param int $customerId
     *
     * @return array
     */
    private function searchIdsByTermAndCustomer($search, $firstResult, $maxResults, $customerId = null)
    {
        $query = $this->indexer->getSimpleSearchQuery($search, $firstResult, $maxResults, $this->entitySearchAlias);
        if ($customerId) {
            $field = Criteria::implodeFieldTypeName(Query::TYPE_INTEGER, 'customer_id');

            $query->getCriteria()->andWhere(Criteria::expr()->eq($field, $customerId));
        }

        $result = $this->indexer->query($query);

        return array_map(
            function (Item $element) {
                return $element->getRecordId();
            },
            $result->getElements()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function findById($query)
    {
        $parts = explode(self::DELIMITER, $query);
        $id = $parts[0];
        $customerId = !empty($parts[1]) ? $parts[1] : false;

        $criteria = [$this->idFieldName => $id];
        if (false !== $customerId) {
            $criteria['customer'] = $customerId;
        }

        return [$this->entityRepository->findOneBy($criteria, null)];
    }
}
