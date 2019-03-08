<?php

namespace Oro\Bundle\CustomerBundle\Handler;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectRepository;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\Repository\ResettableCustomerUserRepositoryInterface;
use Oro\Bundle\DataAuditBundle\Async\Topics;
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

    /**
     * @param ManagerRegistry $registry
     * @param EntityToEntityChangeArrayConverter $entityToArrayConverter
     * @param AuditMessageBodyProvider $auditMessageBodyProvider
     * @param MessageProducerInterface $messageProducer
     * @param TokenStorageInterface $tokenStorage
     */
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

    /**
     * @param string $entityClass
     */
    public function setEntityClass(string $entityClass)
    {
        $this->entityClass = $entityClass;
    }

    /**
     * @param CustomerUser $customerUser
     * @throws \Oro\Component\MessageQueue\Transport\Exception\Exception
     */
    public function update(CustomerUser $customerUser)
    {
        $entityCount = $this->getRepository()->getRelatedEntitiesCount($customerUser);

        if (!$entityCount) {
            return;
        }

        $pageCount = ceil($entityCount/self::BATCH_SIZE);

        for ($page = 0; $page < $pageCount; $page++) {
            $this->batchResetAndSendUpdates($customerUser, $page);
        }
    }

    /**
     * @param CustomerUser $customerUser
     * @param int $page
     */
    private function batchResetAndSendUpdates(CustomerUser $customerUser, int $page)
    {
        $repository = $this->getRepository();

        $entitiesToUpdate = $repository->findBy(
            ['customerUser' => $customerUser],
            null,
            self::BATCH_SIZE,
            $page * self::BATCH_SIZE
        );

        $repository->resetCustomerUser($customerUser, $entitiesToUpdate);

        $updates = $this->getUpdates($customerUser, $entitiesToUpdate);

        $body = $this->auditMessageBodyProvider->prepareMessageBody(
            [],
            $updates,
            [],
            [],
            $this->tokenStorage->getToken()
        );

        $this->messageProducer->send(
            Topics::ENTITIES_CHANGED,
            new Message($body, MessagePriority::VERY_LOW)
        );
    }

    /**
     * @param CustomerUser $customerUser
     * @return bool
     */
    public function hasEntitiesToUpdate(CustomerUser $customerUser): bool
    {
        return (bool)$this->getRepository()->getRelatedEntitiesCount($customerUser);
    }

    /**
     * @return string
     */
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

    /**
     * @param CustomerUser $oldCustomerUser
     * @param array $entities
     * @return array
     */
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
}
