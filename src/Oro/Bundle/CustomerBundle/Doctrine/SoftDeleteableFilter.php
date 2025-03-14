<?php

namespace Oro\Bundle\CustomerBundle\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

/**
 * All "softly" deleted entities should be removed from all queries.
 */
class SoftDeleteableFilter extends SQLFilter
{
    public const string FILTER_ID = 'soft_deleteable';

    private ?EntityManagerInterface $em = null;

    #[\Override]
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if (!$targetEntity->reflClass->implementsInterface(SoftDeleteableInterface::NAME)) {
            return '';
        }

        $connection = $this->getEm()->getConnection();
        $platform = $connection->getDatabasePlatform();
        $column = $this->getEm()
            ->getConfiguration()
            ->getQuoteStrategy()
            ->getColumnName(SoftDeleteableInterface::FIELD_NAME, $targetEntity, $platform);

        return $platform->getIsNullExpression($targetTableAlias . '.' . $column);
    }

    public function setEm(EntityManagerInterface $em): void
    {
        $this->em = $em;
    }

    public function getEm(): EntityManagerInterface
    {
        if (!$this->em) {
            throw new \InvalidArgumentException('EntityManager injection required.');
        }

        return $this->em;
    }
}
