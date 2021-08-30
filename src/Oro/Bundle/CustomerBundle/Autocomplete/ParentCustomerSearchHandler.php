<?php

namespace Oro\Bundle\CustomerBundle\Autocomplete;

use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerRepository;
use Oro\Bundle\FormBundle\Autocomplete\SearchHandler;

/**
 * The search handler to search by a parent customer.
 */
class ParentCustomerSearchHandler extends SearchHandler
{
    const DELIMITER = ';';

    /**
     * {@inheritdoc}
     */
    public function search($query, $page, $perPage, $searchById = false)
    {
        $searchResult = parent::search($query, $page, $perPage, $searchById);
        if (false === $searchById) {
            if (strpos($query, self::DELIMITER) === false) {
                return $searchResult;
            }

            [, $customerId] = $this->explodeSearchTerm($query);
            if (!$customerId) {
                return $searchResult;
            }

            $idFieldName = $this->idFieldName;
            /** @var CustomerRepository $repository */
            $repository = $this->entityRepository;
            $children = $repository->getChildrenIds($customerId, $this->aclHelper);
            $excludedIds = \array_merge($children, [$customerId]);

            $searchResult['results'] = \array_values(\array_filter(
                $searchResult['results'],
                static function (array $resultItem) use ($idFieldName, $excludedIds) {
                    return !\in_array($resultItem[$idFieldName], $excludedIds, true);
                }
            ));
        }

        return $searchResult;
    }

    /**
     * {@inheritdoc}
     */
    protected function searchEntities($search, $firstResult, $maxResults)
    {
        if (strpos($search, self::DELIMITER) === false) {
            return [];
        }

        [$searchTerm,] = $this->explodeSearchTerm($search);
        return parent::searchEntities($searchTerm, $firstResult, $maxResults);
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
}
