<?php

namespace Oro\Bundle\CustomerBundle\Autocomplete;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FormBundle\Autocomplete\SearchHandler;
use Oro\Bundle\LocaleBundle\DependencyInjection\Configuration;
use Oro\Bundle\SearchBundle\Query\Criteria\Criteria;
use Oro\Bundle\SearchBundle\Query\Query;
use Oro\Bundle\SearchBundle\Query\Result\Item;
use Oro\Component\DoctrineUtils\ORM\QueryBuilderUtil;

/**
 * The autocomplete handler to search enabled localizations.
 */
class EnabledLocalizationsSearchHandler extends SearchHandler
{
    private const DELIMITER = ';';

    private ConfigManager $configManager;

    public function __construct(string $entityName, array $properties, ConfigManager $configManager)
    {
        parent::__construct($entityName, $properties);
        $this->configManager = $configManager;
    }

    /**
     * @return array [Localization]
     */
    protected function searchEntities($search, $firstResult, $maxResults): array
    {
        if (!str_contains($search, static::DELIMITER)) {
            return [];
        }

        $search = $this->normalizeSearch($search);
        $entityIds = $this->searchIdsByTerm($search, $firstResult, $maxResults);
        if (!$entityIds) {
            return [];
        }

        $queryBuilder = $this->entityRepository->createQueryBuilder('l');
        $queryBuilder->where($queryBuilder->expr()->in(
            QueryBuilderUtil::getField('l', $this->idFieldName),
            ':entityIds'
        ));
        $queryBuilder->setParameter('entityIds', $entityIds);
        $query = $this->aclHelper->apply($queryBuilder);

        return $query->getResult();
    }

    /**
     * Overwrites parent method mo make grid search work correct considering new delimiter ";" which divides
     * localization id and website
     * {@inheritdoc}
     */
    protected function findById($query): array
    {
        // By ";" - delimiter. Calling this method assumes we search only for ONE entity
        $localizationId = (int) $this->normalizeSearch($query);
        $enabledLocalizationIds = $this->getEnabledLocalizations();

        //Check if searched id does not exist in current configuration no need to query
        if (!\in_array($localizationId, $enabledLocalizationIds)) {
            return [];
        }

        return $this->getEntitiesByIds([$localizationId]);
    }

    /**
     * @return array [int $websiteId]
     */
    private function searchIdsByTerm(
        string $search,
        int $firstResult,
        int $maxResults
    ): array {
        $query = $this->indexer->getSimpleSearchQuery($search, $firstResult, $maxResults, $this->entitySearchAlias);
        $query->getCriteria()->andWhere(Criteria::expr()->in(
            Criteria::implodeFieldTypeName(Query::TYPE_INTEGER, 'id'),
            $this->getEnabledLocalizations()
        ));

        $result = $this->indexer->query($query);

        return array_map(fn (Item $item) => $item->getRecordId(), $result->getElements());
    }

    /**
     * @return null|array [int $websiteId]
     */
    private function getEnabledLocalizations(): ?array
    {
        $key = Configuration::getConfigKeyByName(Configuration::ENABLED_LOCALIZATIONS);

        return $this->configManager->get($key);
    }

    private function normalizeSearch(string $search): string
    {
        $search = explode(static::DELIMITER, $search, 2);

        return reset($search);
    }
}
