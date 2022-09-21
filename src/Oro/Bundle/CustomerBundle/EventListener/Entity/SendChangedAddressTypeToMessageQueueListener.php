<?php

namespace Oro\Bundle\CustomerBundle\EventListener\Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\PersistentCollection;
use Oro\Bundle\CustomerBundle\Entity\AbstractAddressToAddressType as AddressType;
use Oro\Bundle\DataAuditBundle\Async\Topic\AuditChangedEntitiesTopic;
use Oro\Bundle\DataAuditBundle\Provider\AuditConfigProvider;
use Oro\Bundle\DataAuditBundle\Provider\AuditMessageBodyProvider;
use Oro\Bundle\DataAuditBundle\Service\EntityToEntityChangeArrayConverter;
use Oro\Bundle\DistributionBundle\Handler\ApplicationState;
use Oro\Bundle\PlatformBundle\EventListener\OptionalListenerInterface;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Audit listener for the Address types attribute.
 */
class SendChangedAddressTypeToMessageQueueListener implements OptionalListenerInterface
{
    private const BATCH_SIZE = 100;
    private const ADDRESS_TYPE_FIELD = 'types';
    private const CUSTOMER_ADDRESS_FIELD = 'addresses';

    private const ACTION_INSERTED = 'inserted';
    private const ACTION_CHANGED  = 'changed';
    private const ACTION_DELETED  = 'deleted';

    private const CHANGE_SET = 'change_set';

    private MessageProducerInterface $messageProducer;
    private TokenStorageInterface $tokenStorage;
    private EntityToEntityChangeArrayConverter $entityToArrayConverter;
    private AuditConfigProvider $auditConfigProvider;
    private AuditMessageBodyProvider $auditMessageBodyProvider;
    private ApplicationState $applicationState;

    private \SplObjectStorage $allTokens;

    private array $collectionInserts;
    private array $collectionChanges;
    private array $collectionDeletes;

    private bool $enabled = true;

    private string $addressClass;
    private string $addressTypeClass;

    public function __construct(
        MessageProducerInterface $messageProducer,
        TokenStorageInterface $tokenStorage,
        EntityToEntityChangeArrayConverter $entityToArrayConverter,
        AuditConfigProvider $auditConfigProvider,
        AuditMessageBodyProvider $auditMessageBodyProvider,
        ApplicationState $applicationState,
        string $addressClass,
        string $addressTypeClass
    ) {
        $this->messageProducer = $messageProducer;
        $this->tokenStorage = $tokenStorage;
        $this->entityToArrayConverter = $entityToArrayConverter;
        $this->auditConfigProvider = $auditConfigProvider;
        $this->auditMessageBodyProvider = $auditMessageBodyProvider;
        $this->applicationState = $applicationState;

        $this->allTokens = new \SplObjectStorage;

        $this->collectionInserts = [];
        $this->collectionChanges = [];
        $this->collectionDeletes = [];

        $this->addressClass = $addressClass;
        $this->addressTypeClass = $addressTypeClass;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabled($enabled = true)
    {
        $this->enabled = $enabled;
    }

    private function isEnabled(): bool
    {
        return $this->enabled &&
            $this->applicationState->isInstalled() &&
            $this->auditConfigProvider->isAuditableField(
                $this->addressClass,
                self::ADDRESS_TYPE_FIELD
            );
    }

    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        if (!$this->isEnabled()) {
            return;
        }

        $em = $eventArgs->getEntityManager();

        $this->prepareScheduledEntityUpdates($em);
        $this->prepareScheduledCollectionUpdates($em);

        $token = $this->tokenStorage->getToken();
        if (null !== $token) {
            $this->allTokens[$em] = $token;
        }
    }

    private function prepareScheduledEntityUpdates(EntityManager $em)
    {
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof $this->addressTypeClass) {
                $this->addEntityUpdate($em, $entity);
            }
        }
    }

    private function prepareScheduledCollectionUpdates(EntityManager $em)
    {
        $uow = $em->getUnitOfWork();

        /** @var PersistentCollection $collection */
        foreach ($uow->getScheduledCollectionUpdates() as $collection) {
            if ($collection->getOwner() instanceof $this->addressClass
                && self::ADDRESS_TYPE_FIELD === $collection->getMapping()['fieldName']
            ) {
                /** @var AddressType $entity */
                foreach ($collection->getInsertDiff() as $entity) {
                    $this->addCollectionInsert($em, $entity);
                }

                foreach ($collection->getDeleteDiff() as $entity) {
                    $this->addCollectionDeletion($em, $entity);
                }
            }
        }
    }

    public function postFlush(PostFlushEventArgs $eventArgs)
    {
        if (!$this->isEnabled()) {
            return;
        }

        $em = $eventArgs->getEntityManager();

        try {
            [$updates, $collectionUpdates] = $this->processUpdates($em);
            do {
                $body = $this->auditMessageBodyProvider->prepareMessageBody(
                    [],
                    array_splice($updates, 0, self::BATCH_SIZE),
                    [],
                    array_splice($collectionUpdates, 0, self::BATCH_SIZE),
                    $this->getSecurityToken($em)
                );

                if (!empty($body)) {
                    $this->messageProducer->send(
                        AuditChangedEntitiesTopic::getName(),
                        new Message($body, MessagePriority::VERY_LOW)
                    );
                }
            } while ($body);
        } finally {
            unset($this->collectionChanges[$this->getEntityHash($em)]);
            unset($this->collectionInserts[$this->getEntityHash($em)]);
            unset($this->collectionDeletes[$this->getEntityHash($em)]);
            $this->allTokens->detach($em);
        }
    }

    private function addEntityUpdate(EntityManager $em, AddressType $addressType)
    {
        $changeSet = $em->getUnitOfWork()->getEntityChangeSet($addressType);
        $entityName = $addressType->getType()->getName();
        $this->collectionChanges[$this->getEntityHash($em)][] = [
            $addressType,
            $this->convertEntityToArray($em, $addressType, $changeSet, $entityName),
        ];
    }

    private function addCollectionInsert(EntityManager $em, AddressType $addressType)
    {
        $this->collectionInserts[$this->getEntityHash($em)][] = $addressType;
    }

    private function addCollectionDeletion(EntityManager $em, AddressType $addressType)
    {
        $changeSet = [
            self::ADDRESS_TYPE_FIELD => [$addressType->getType()->getName(), null],
        ];
        $entityName = $addressType->getType()->getName();
        $this->collectionDeletes[$this->getEntityHash($em)][] = [
            $addressType,
            $this->convertEntityToArray($em, $addressType, $changeSet, $entityName),
        ];
    }

    private function getSecurityToken(EntityManager $em): ?TokenInterface
    {
        return $this->allTokens->contains($em) ? $this->allTokens[$em] : $this->tokenStorage->getToken();
    }

    private function processUpdates(EntityManager $em): array
    {
        /** @var AddressType $addressType */
        $updates = [];
        $collectionUpdates = [];
        $emId = $this->getEntityHash($em);

        if (isset($this->collectionInserts[$emId])) {
            foreach ($this->collectionInserts[$emId] as $addressType) {
                $this->addOwnerChangeSet($em, $addressType, $updates, self::ACTION_INSERTED);
                $this->addAddressChangeSet($em, $addressType, $collectionUpdates, self::ACTION_INSERTED);
            }
        }

        if (isset($this->collectionChanges[$emId])) {
            foreach ($this->collectionChanges[$emId] as $args) {
                [$addressType, $data] = $args;
                $this->addOwnerChangeSet($em, $addressType, $updates, self::ACTION_CHANGED, $data);
                $this->addAddressChangeSet(
                    $em,
                    $addressType,
                    $collectionUpdates,
                    self::ACTION_CHANGED,
                    $data
                );
            }
        }

        if (isset($this->collectionDeletes[$emId])) {
            foreach ($this->collectionDeletes[$emId] as $args) {
                [$addressType, $data] = $args;
                $this->addOwnerChangeSet($em, $addressType, $updates, self::ACTION_DELETED, $data);
                $this->addAddressChangeSet(
                    $em,
                    $addressType,
                    $collectionUpdates,
                    self::ACTION_DELETED,
                    $data
                );
            }
        }

        return [$updates, $collectionUpdates];
    }

    private function addOwnerChangeSet(
        EntityManager $em,
        AddressType $addressType,
        array &$updates,
        string $action,
        array $changeSet = null
    ) {
        $this->ensureOwnerChangeSetRoot($em, $addressType, $updates);

        $customer = $addressType->getAddress()->getFrontendOwner();
        $actionIndex = self::ACTION_DELETED === $action ? 0 : 1;
        $customerId = $this->getEntityHash($customer);

        if (!$changeSet) {
            $changeSet = $this->convertEntityToArray($em, $addressType, [], $addressType->getType()->getName());
        }

        $updates[$customerId][self::CHANGE_SET][self::CUSTOMER_ADDRESS_FIELD][$actionIndex][$action][] = $changeSet;
    }

    private function ensureOwnerChangeSetRoot(EntityManager $em, AddressType $addressType, array &$updates)
    {
        $customer = $addressType->getAddress()->getFrontendOwner();
        $customerId = $this->getEntityHash($customer);

        if (!isset($updates[$customerId])) {
            $updates[$customerId] = $this->convertEntityToArray($em, $customer, []);
            $updates[$customerId][self::CHANGE_SET][self::CUSTOMER_ADDRESS_FIELD] = [[], []];
        }
    }

    private function addAddressChangeSet(
        EntityManager $em,
        AddressType $addressType,
        array &$updates,
        string $action,
        array $changeSet = null
    ) {
        $this->ensureAddressChangeSetRoot($em, $addressType, $updates);

        $actionIndex = self::ACTION_DELETED === $action ? 0 : 1;
        $addressId = $this->getEntityHash($addressType->getAddress());

        if (!$changeSet) {
            $changeSet = $this->convertEntityToArray($em, $addressType, [], $addressType->getType()->getName());
        }

        $updates[$addressId][self::CHANGE_SET][self::ADDRESS_TYPE_FIELD][$actionIndex][$action][] = $changeSet;
    }

    private function ensureAddressChangeSetRoot(EntityManager $em, AddressType $addressType, array &$updates)
    {
        $addressId = $this->getEntityHash($addressType->getAddress());

        if (!isset($updates[$addressId])) {
            $updates[$addressId] = $this->convertEntityToArray($em, $addressType->getAddress(), []);
            $updates[$addressId][self::CHANGE_SET][self::ADDRESS_TYPE_FIELD] = [[], []];
        }
    }

    private function convertEntityToArray(EntityManager $em, $entity, array $changeSet, $entityName = null): array
    {
        return $this->entityToArrayConverter->convertNamedEntityToArray($em, $entity, $changeSet, $entityName);
    }

    private function getEntityHash($entity): string
    {
        return spl_object_hash($entity);
    }
}
