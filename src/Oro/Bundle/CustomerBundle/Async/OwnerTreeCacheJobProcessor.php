<?php

namespace Oro\Bundle\CustomerBundle\Async;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Async\Topic\CustomerCalculateOwnerTreeCacheByBusinessUnitTopic;
use Oro\Bundle\CustomerBundle\Async\Topic\CustomerCalculateOwnerTreeCacheTopic;
use Oro\Bundle\CustomerBundle\Model\BusinessUnitMessageFactory;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\Job;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * Warms up cache for all customer user's who logged in during cache ttl time period.
 */
class OwnerTreeCacheJobProcessor implements MessageProcessorInterface, TopicSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private JobRunner $jobRunner;

    private MessageProducerInterface $producer;

    private ManagerRegistry $doctrine;

    private OwnershipMetadataProviderInterface $ownershipMetadataProvider;

    private BusinessUnitMessageFactory $businessUnitMessageFactory;

    public function __construct(
        JobRunner $jobRunner,
        MessageProducerInterface $producer,
        ManagerRegistry $doctrine,
        OwnershipMetadataProviderInterface $ownershipMetadataProvider,
        BusinessUnitMessageFactory $businessUnitMessageFactory
    ) {
        $this->jobRunner = $jobRunner;
        $this->producer = $producer;
        $this->doctrine = $doctrine;
        $this->ownershipMetadataProvider = $ownershipMetadataProvider;
        $this->businessUnitMessageFactory = $businessUnitMessageFactory;

        $this->logger = new NullLogger();
    }

    public function process(MessageInterface $message, SessionInterface $session): string
    {
        $messageBody = $message->getBody();
        $cacheTtl = $messageBody[CustomerCalculateOwnerTreeCacheTopic::CACHE_TTL];
        $result = $this->jobRunner->runUniqueByMessage(
            $message,
            function (JobRunner $jobRunner) use ($cacheTtl) {
                $userClass = $this->ownershipMetadataProvider->getUserClass();

                /** @var QueryBuilder $queryBuilder */
                $queryBuilder = $this->doctrine->getRepository($userClass)->createQueryBuilder('cu');

                $dateTime = (new \DateTime())->sub(new \DateInterval(sprintf('PT%dS', $cacheTtl)));

                $customerIds = $queryBuilder
                    ->distinct()
                    ->select('IDENTITY(cu.customer)')
                    ->andWhere($queryBuilder->expr()->gt('cu.lastLogin', ':dateTime'))
                    ->getQuery()
                    ->setParameter('dateTime', $dateTime, Types::DATETIME_MUTABLE)
                    ->getScalarResult();

                $businessUnitClass = $this->ownershipMetadataProvider->getBusinessUnitClass();
                foreach ($customerIds as $customerId) {
                    $this->scheduleCacheRecalculationForCustomer(
                        $jobRunner,
                        $businessUnitClass,
                        reset($customerId)
                    );
                }

                return true;
            }
        );

        return $result ? self::ACK : self::REJECT;
    }

    private function scheduleCacheRecalculationForCustomer(
        JobRunner $jobRunner,
        string $entityClass,
        int $entityId
    ): void {
        $jobRunner->createDelayed(
            sprintf('%s:%s:%s', CustomerCalculateOwnerTreeCacheTopic::getName(), $entityClass, $entityId),
            function (JobRunner $jobRunner, Job $child) use ($entityClass, $entityId) {
                $messageData = $this->businessUnitMessageFactory
                    ->createMessage($child->getId(), $entityClass, $entityId);

                $this->producer->send(CustomerCalculateOwnerTreeCacheByBusinessUnitTopic::getName(), $messageData);
            }
        );
    }

    public static function getSubscribedTopics(): array
    {
        return [CustomerCalculateOwnerTreeCacheTopic::getName()];
    }
}
