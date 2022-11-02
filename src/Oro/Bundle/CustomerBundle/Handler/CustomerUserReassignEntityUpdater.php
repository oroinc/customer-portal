<?php

namespace Oro\Bundle\CustomerBundle\Handler;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\Repository\ResettableCustomerUserRepositoryInterface;
use Oro\Bundle\DataAuditBundle\Async\Topic\AuditChangedEntitiesTopic;
use Oro\Bundle\DataAuditBundle\Provider\AuditMessageBodyProvider;
use Oro\Bundle\DataAuditBundle\Service\EntityToEntityChangeArrayConverter;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Execute customer user reset for given entity on customer user reassign.
 */
class CustomerUserReassignEntityUpdater
{
    const BATCH_SIZE = 100;

    /** @var ManagerRegistry */
    private $registry;

    /** @var string */
    private $entityClass;

    /** @var EntityToEntityChangeArrayConverter */
    private $entityToArrayConverter;

    /** @var AuditMessageBodyProvider */
    private $auditMessageBodyProvider;

    /** @var MessageProducerInterface */
    private $messageProducer;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        ManagerRegistry $registry,
        EntityToEntityChangeArrayConverter $entityToArrayConverter,
        AuditMessageBodyProvider $auditMessageBodyProvider,
        MessageProducerInterface $messageProducer,
        TokenStorageInterface $tokenStorage
    ) {
        $this->registry = $registry;
        $this->entityToArrayConverter = $entityToArrayConverter;
        $this->auditMessageBodyProvider = $auditMessageBodyProvider;
        $this->messageProducer = $messageProducer;
        $this->tokenStorage = $tokenStorage;
    }

    public function setEntityClass(string $entityClass)
    {
        $this->entityClass = $entityClass;
    }

    /**
     * @throws \Oro\Component\MessageQueue\Transport\Exception\Exception
     */
    public function update(CustomerUser $customerUser)
    {
        $entityCount = $this->getRepository()->getRelatedEntitiesCount($customerUser);
        if (!$entityCount) {
            return;
        }

        do {
            $hasUpdates = $this->batchResetAndSendUpdates($customerUser);
        } while ($hasUpdates);
    }

    private function batchResetAndSendUpdates(CustomerUser $customerUser)
    {
        $repository = $this->getRepository();

        $entitiesToUpdate = $repository->findBy(
            ['customerUser' => $customerUser],
            null,
            self::BATCH_SIZE
        );

        $updated = 0;
        if ($entitiesToUpdate) {
            $updated = $repository->resetCustomerUser($customerUser, $entitiesToUpdate);
            $this->sendAuditMessage($customerUser, $entitiesToUpdate);
        }

        return $updated;
    }

    public function hasEntitiesToUpdate(CustomerUser $customerUser): bool
    {
        return (bool)$this->getRepository()->getRelatedEntitiesCount($customerUser);
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    /**
     * @return ResettableCustomerUserRepositoryInterface|ObjectRepository
     */
    private function getRepository(): ResettableCustomerUserRepositoryInterface
    {
        return $this->registry->getManagerForClass($this->entityClass)
            ->getRepository($this->entityClass);
    }

    private function getUpdates(CustomerUser $oldCustomerUser, array $entities): array
    {
        $em = $this->registry->getManagerForClass($this->entityClass);

        $updates = [];
        $changeSet = [
            'customerUser' => [
                $oldCustomerUser,
                null,
            ]
        ];
        foreach ($entities as $entity) {
            $updates[] = $this->entityToArrayConverter->convertEntityToArray($em, $entity, $changeSet);
        }

        return $updates;
    }

    private function sendAuditMessage(CustomerUser $customerUser, array $entitiesToUpdate): void
    {
        $updates = $this->getUpdates($customerUser, $entitiesToUpdate);
        if ($updates) {
            $body = $this->auditMessageBodyProvider->prepareMessageBody(
                [],
                $updates,
                [],
                [],
                $this->tokenStorage->getToken()
            );

            $this->messageProducer->send(
                AuditChangedEntitiesTopic::getName(),
                new Message($body, MessagePriority::VERY_LOW)
            );
        }
    }
}
