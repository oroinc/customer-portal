<?php

namespace Oro\Bundle\CustomerBundle\Security\Listener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Cache\DoctrineAclCacheProvider;
use Oro\Bundle\SecurityBundle\Owner\OwnerTreeInterface;
use Oro\Bundle\SecurityBundle\Owner\OwnerTreeProviderInterface;
use Symfony\Component\Security\Acl\Util\ClassUtils;

/**
 * Clear Doctrine ACL query cache to be sure that queries will process hints
 * again with updated security information.
 */
class CustomerUserDoctrineAclCacheListener
{
    private DoctrineAclCacheProvider $queryCacheProvider;
    private OwnerTreeProviderInterface $ownerTreeProvider;

    private bool $isCacheOutdated = false;
    private ?OwnerTreeInterface $ownerTree = null;

    /**
     * @var array [className => [fieldName => shouldValueBeCheckedOnBoolean, ...], ...]
     */
    private array $entitiesShouldBeProcessedByUpdate = [
        Customer::class => ['parent' => false],
        CustomerUser::class => ['customer' => false]
    ];

    public function __construct(
        DoctrineAclCacheProvider $queryCacheProvider,
        OwnerTreeProviderInterface $ownerTreeProvider
    ) {
        $this->queryCacheProvider = $queryCacheProvider;
        $this->ownerTreeProvider = $ownerTreeProvider;
    }

    public function addEntityShouldBeProcessedByUpdate(string $entityClass, array $fieldNames): void
    {
        if (!\array_key_exists($entityClass, $this->entitiesShouldBeProcessedByUpdate)) {
            $this->entitiesShouldBeProcessedByUpdate[$entityClass] = [];
        }

        $this->entitiesShouldBeProcessedByUpdate[$entityClass] = array_merge(
            $this->entitiesShouldBeProcessedByUpdate[$entityClass],
            $fieldNames
        );
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        if ($this->isCacheOutdated) {
            return;
        }

        $em = $args->getObjectManager();
        $this->ownerTree = null;
        try {
            $changedEntities = $this->getChangedEntities($em->getUnitOfWork());
            $this->isCacheOutdated = count($changedEntities) > 0;

            if ($this->isCacheOutdated) {
                $this->queryCacheProvider->clearForEntities(Customer::class, $changedEntities);
            }
        } finally {
            $this->ownerTree = null;
        }
    }

    private function getChangedEntities(UnitOfWork $uow): array
    {
        $customerUsersToBreakTheCache = [];
        $customerUsersToBreakTheCache[] = $this->getCustomerUsersShouldBeUpdatedByInsertions($uow);
        $customerUsersToBreakTheCache[] = $this->getCustomerUsersShouldBeUpdatedByUpdates($uow);
        $customerUsersToBreakTheCache[] = $this->getCustomerUsersShouldBeUpdatedByDeletions($uow);
        $customerUsersToBreakTheCache[] = $this->getCustomerUsersShouldBeUpdatedByCollectionUpdates($uow);

        return array_unique(array_merge(...$customerUsersToBreakTheCache));
    }

    private function getCustomerUsersShouldBeUpdatedByInsertions(UnitOfWork $uow): array
    {
        $customersToBreakTheCache = [];
        $insertedCustomerUsers = $this->getInsertedOrDeletedEntities(
            $uow->getScheduledEntityInsertions(),
            [CustomerUser::class]
        );
        foreach ($insertedCustomerUsers as $insertedEntity) {
            $parentCustomers = [];
            $this->collectParentCustomerIds($insertedEntity->getCustomer(), $parentCustomers);
            $customersToBreakTheCache[] = $parentCustomers;
        }

        return array_unique(array_merge(...$customersToBreakTheCache));
    }

    private function getCustomerUsersShouldBeUpdatedByDeletions(UnitOfWork $uow): array
    {
        $customersToBreakTheCache = [];
        $deletedCustomers = $this->getInsertedOrDeletedEntities(
            $uow->getScheduledEntityDeletions(),
            [Customer::class]
        );
        foreach ($deletedCustomers as $deletedEntity) {
            $parentCustomers = [];
            $this->collectParentCustomerIds($deletedEntity, $parentCustomers);
            $customersToBreakTheCache[] = array_merge(
                $parentCustomers,
                [$deletedEntity->getId()],
                $this->getOwnerTree()->getSubordinateBusinessUnitIds($deletedEntity->getId())
            );
        }

        return array_unique(array_merge(...$customersToBreakTheCache));
    }

    private function getCustomerUsersShouldBeUpdatedByUpdates(UnitOfWork $uow): array
    {
        $customersToBreakTheCache = [];
        $updatedEntities = $this->getUpdatedEntities($uow, $this->entitiesShouldBeProcessedByUpdate);
        foreach ($updatedEntities as $updatesEntityData) {
            [$entity, $changeSet] = $updatesEntityData;
            if ($entity instanceof Customer) {
                $oldParents = $newParents = [];
                if ($changeSet[0]) {
                    $this->collectParentCustomerIds($changeSet[0], $oldParents);
                }
                if ($changeSet[1]) {
                    $this->collectParentCustomerIds($changeSet[1], $newParents);
                }

                $customersToBreakTheCache[] = array_unique(array_merge(
                    $oldParents,
                    $newParents,
                    [$entity->getId()]
                ));
            } elseif ($entity instanceof CustomerUser) {
                $oldParents = $newParents = [];
                if ($changeSet[0]) {
                    $this->collectParentCustomerIds($changeSet[0], $oldParents);
                }
                if ($changeSet[1]) {
                    $this->collectParentCustomerIds($changeSet[1], $newParents);
                }

                $customersToBreakTheCache[] = array_unique(array_merge(
                    $oldParents,
                    $newParents
                ));
            } elseif ($entity instanceof Organization) {
                $customersToBreakTheCache[] = $this->getOwnerTree()->getOrganizationBusinessUnitIds($entity->getId());
            }
        }

        return array_unique(array_merge(...$customersToBreakTheCache));
    }

    private function getCustomerUsersShouldBeUpdatedByCollectionUpdates(
        UnitOfWork $uow
    ): array {
        $customersToBreakTheCache = [];
        $updatedRelations = $this->getToManyRelations(
            $uow->getScheduledCollectionUpdates(),
            [
                CustomerUser::class => ['userRoles']
            ]
        );
        foreach ($updatedRelations as $entity) {
            /** @var $collection PersistentCollection */
            $customersToBreakTheCache[] = [$entity->getCustomer()->getId()];
        }

        return array_unique(array_merge(...$customersToBreakTheCache));
    }

    private function getInsertedOrDeletedEntities(array $entities, array $supportedClasses): array
    {
        $changedEntities = [];
        foreach ($entities as $entity) {
            if (\in_array(ClassUtils::getRealClass($entity), $supportedClasses, true)) {
                $changedEntities[] = $entity;
            }
        }

        return $changedEntities;
    }

    private function getUpdatedEntities(UnitOfWork $uow, array $supportedClasses): array
    {
        $changedEntities = [];
        $entities = $uow->getScheduledEntityUpdates();
        foreach ($entities as $entity) {
            $entityClass = ClassUtils::getRealClass($entity);
            if (!\array_key_exists($entityClass, $supportedClasses)) {
                continue;
            }

            $fields = array_keys($supportedClasses[$entityClass]);
            $changeSet = $uow->getEntityChangeSet($entity);
            foreach ($fields as $fieldName) {
                if (\array_key_exists($fieldName, $changeSet)) {
                    if ($supportedClasses[$entityClass][$fieldName] === true
                        && (bool)$changeSet[$fieldName][0] === (bool)$changeSet[$fieldName][1]
                    ) {
                        continue;
                    }
                    $changedEntities[] = [$entity, $changeSet[$fieldName]];
                }
            }
        }

        return $changedEntities;
    }

    private function getToManyRelations(array $collections, array $supportedClasses): array
    {
        $changedEntities = [];
        /** @var PersistentCollection $collection */
        foreach ($collections as $collection) {
            $entity = $collection->getOwner();
            $entityClass = ClassUtils::getRealClass($entity);
            if (!\array_key_exists($entityClass, $supportedClasses)) {
                continue;
            }

            $associations = $supportedClasses[$entityClass];
            if ($associations) {
                $associationMapping = $collection->getMapping();
                if (\in_array($associationMapping['fieldName'], $associations, true)) {
                    $changedEntities[] = $entity;
                }
            }
        }

        return $changedEntities;
    }

    private function collectParentCustomerIds(Customer $customer, array &$customerIds): void
    {
        $customerIds[] = $customer->getId();
        if ($customer->getParent()) {
            $this->collectParentCustomerIds($customer->getParent(), $customerIds);
        }
    }

    private function getOwnerTree(): OwnerTreeInterface
    {
        if (null === $this->ownerTree) {
            $this->ownerTree = $this->ownerTreeProvider->getTree();
        }

        return $this->ownerTree;
    }
}
