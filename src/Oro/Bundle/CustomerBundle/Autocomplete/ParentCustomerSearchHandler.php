<?php

namespace Oro\Bundle\CustomerBundle\Autocomplete;

use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerRepository;
use Oro\Bundle\FormBundle\Autocomplete\SearchHandler;
use Oro\Bundle\SearchBundle\Query\Criteria\Criteria;
use Oro\Bundle\SearchBundle\Query\Query;
use Oro\Bundle\SearchBundle\Query\Result\Item;

/**
 * The search handler to search by a parent customer.
 */
class ParentCustomerSearchHandler extends SearchHandler
{
    const DELIMITER = ';';

    /**
     * {@inheritdoc}
     */
    protected function searchEntities($search, $firstResult, $maxResults)
    {
        if (!str_contains($search, self::DELIMITER)) {
            return [];
        }

        [$searchTerm, $customerId] = $this->explodeSearchTerm($search);
        $excludedIds = [];
        if ($customerId) {
            /** @var CustomerRepository $repository */
            $repository = $this->entityRepository;
            $children = $repository->getChildrenIds($customerId, $this->aclHelper);
            $excludedIds = \array_merge($children, [$customerId]);
        }

        $entityIds = $this->searchIdsByTermAndExcludedCustomers(
            $searchTerm,
            $firstResult,
            $maxResults,
            $excludedIds
        );

        $resultEntities = [];
        if ($entityIds) {
            $unsortedEntities = $this->getEntitiesByIds($entityIds);

            /**
             * We need to sort entities in the same order given by method searchIds.
             *
             * Should be not necessary after implementation of BAP-5691.
             */
            $entityByIdHash = [];

            foreach ($unsortedEntities as $entity) {
                $entityByIdHash[$this->getPropertyValue($this->idFieldName, $entity)] = $entity;
            }

            foreach ($entityIds as $entityId) {
                if (isset($entityByIdHash[$entityId])) {
                    $resultEntities[] = $entityByIdHash[$entityId];
                }
            }
        }

        return $resultEntities;
    }

    /**
     * @param string $search
     * @return array
     */
    protected function explodeSearchTerm($search)
    {
        $delimiterPos = strrpos($search, self::DELIMITER);
        $searchTerm = substr($search, 0, $delimiterPos);
        $customerId = substr($search, $delimiterPos + 1);
        if ($customerId === false) {
            $customerId = '';
        } else {
            $customerId = (int)$customerId;
        }

        return [$searchTerm, $customerId];
    }

    /**
     * @param string $search
     * @param int $firstResult
     * @param int $maxResults
     * @param int $customerId
     *
     * @return array
     */
    private function searchIdsByTermAndExcludedCustomers(
        string $search,
        int $firstResult,
        int $maxResults,
        array $excludedCustomerIds = []
    ): array {
        $query = $this->indexer->getSimpleSearchQuery($search, $firstResult, $maxResults, $this->entitySearchAlias);
        if ($excludedCustomerIds) {
            $field = Criteria::implodeFieldTypeName(Query::TYPE_INTEGER, 'oro_customer_id');

            $query->getCriteria()->andWhere(Criteria::expr()->notIn($field, $excludedCustomerIds));
        }

        $result = $this->indexer->query($query);

        return array_map(
            static fn (Item $element) => $element->getRecordId(),
            $result->getElements()
        );
    }
}
