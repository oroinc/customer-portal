<?php

namespace Oro\Bundle\CustomerBundle\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

/**
 * Helper that check if given customer is assigned to another entities and cannot be deleted.
 */
class CustomerAssignHelper
{
    /** @var DoctrineHelper */
    private $doctrineHelper;

    /** @var array [class name => [association name, ...], ...] */
    private $ignoredRelations = [];

    /** @var string[] [class name, ...] */
    private $priorityRelations = [];

    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * Adds the relation that should be skipped during the check if Customer has assignments.
     */
    public function addIgnoredRelation(string $className, string $relationName): void
    {
        $this->ignoredRelations[$className][] = $relationName;
    }

    /**
     * Adds an entity class name that should be checked at the first during the check if Customer has assignments.
     */
    public function addPriorityRelation(string $className): void
    {
        $this->priorityRelations[] = $className;
    }

    /**
     * Returns true if given customer is assigned to another entities.
     */
    public function hasAssignments(Customer $customer): bool
    {
        $em = $this->doctrineHelper->getEntityManagerForClass(Customer::class);
        $classNames = $this->getEntityClassNames($em);
        foreach ($classNames as $className) {
            $associations = $this->doctrineHelper->getEntityMetadataForClass($className)->getAssociationMappings();
            foreach ($associations as $association) {
                if ($association['isOwningSide'] && $association['targetEntity'] === Customer::class) {
                    if ($this->isAssociationShouldBeSkipped($className, $association)) {
                        continue;
                    }

                    $result = $em->getRepository($className)
                        ->createQueryBuilder('entity')
                        ->select('entity.id')
                        ->where('entity.' . $association['fieldName'] . ' = :entityId')
                        ->setParameter('entityId', $customer->getId())
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getArrayResult();

                    if (!empty($result)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Gets class names of entities to be checked.
     *
     * @param EntityManagerInterface $em
     *
     * @return string[]
     */
    private function getEntityClassNames(EntityManagerInterface $em): array
    {
        $result = $this->priorityRelations;
        $allShortMetadata = $this->doctrineHelper->getAllShortMetadata($em);
        foreach ($allShortMetadata as $shortMetadata) {
            if (!$shortMetadata->isMappedSuperclass
                && $shortMetadata->hasAssociations
                && !\in_array($shortMetadata->name, $this->priorityRelations, true)
            ) {
                $result[] = $shortMetadata->name;
            }
        }

        return $result;
    }

    /**
     * Check if given association should be skipped.
     */
    private function isAssociationShouldBeSkipped(string $className, array $association): bool
    {
        // skip association if it is in list of ignored relations
        if (isset($this->ignoredRelations[$className])
            && \in_array($association['fieldName'], $this->ignoredRelations[$className], true)
        ) {
            return true;
        }

        // skip to-many association if it is configured to cascade delete
        if ($association['type'] === ClassMetadataInfo::MANY_TO_ONE) {
            return $association['joinColumns'][0]['onDelete'] === 'CASCADE';
        }
        if ($association['type'] === ClassMetadataInfo::MANY_TO_MANY) {
            return $association['joinTable']['inverseJoinColumns'][0]['onDelete'] === 'CASCADE';
        }

        return false;
    }
}
