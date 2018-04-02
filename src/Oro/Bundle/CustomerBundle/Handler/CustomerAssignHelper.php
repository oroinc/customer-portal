<?php

namespace Oro\Bundle\CustomerBundle\Handler;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Oro\Bundle\CustomerBundle\Entity\Customer;

/**
 * Helper that check if given customer is assigned to another entities and cannot be deleted.
 */
class CustomerAssignHelper
{
    /** @var ManagerRegistry */
    protected $registry;

    /**
     * Relations list that should be skipped during checks if Customer has assignments.
     *
     * @var array [className => [relationField1, relationField2, ...], ...]
     */
    protected $ignoredRelations = [];

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Add the relation that should be skipped during checks if Customer has assignments.
     *
     * @param $className
     * @param $relationName
     */
    public function addIgnoredRelation($className, $relationName)
    {
        if (!array_key_exists($className, $this->ignoredRelations)) {
            $this->ignoredRelations[$className] = [];
        }

        $this->ignoredRelations[$className][] = $relationName;
    }

    /**
     * Returns true if given customer is assigned to another entities.
     *
     * @param Customer $customer
     *
     * @return boolean
     */
    public function hasAssignments(Customer $customer)
    {
        /** @var EntityManager $em */
        $em = $this->registry->getManagerForClass(Customer::class);
        /** @var ClassMetadata[] $doctrineAllMetadata */
        $doctrineAllMetadata = $em->getMetadataFactory()->getAllMetadata();
        foreach ($doctrineAllMetadata as $metadata) {
            $className = $metadata->getName();
            $associations = $metadata->getAssociationMappings();
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
     * Check if given association should be skipped.
     *
     * @param string $className
     * @param array $association
     *
     * @return bool
     */
    protected function isAssociationShouldBeSkipped($className, array $association)
    {
        // skip association if it is in list of ignored relations
        if (array_key_exists($className, $this->ignoredRelations)
            && in_array($association['fieldName'], $this->ignoredRelations[$className], true)
        ) {
            return true;
        }

        // skip association if if configured to cascade delete
        if ((
            $association['type'] === ClassMetadataInfo::MANY_TO_ONE
                && $association['joinColumns'][0]['onDelete'] === 'CASCADE'
            )
            || ($association['type'] === ClassMetadataInfo::MANY_TO_MANY
                && $association['joinTable']['inverseJoinColumns'][0]['onDelete'] === 'CASCADE')
        ) {
            return true;
        }

        return false;
    }
}
