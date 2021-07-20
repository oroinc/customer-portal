<?php

namespace Oro\Bundle\CustomerBundle\Async;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Model\BusinessUnitMessageFactory;
use Oro\Bundle\CustomerBundle\Model\OwnerTreeMessageFactory;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\Job;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use Psr\Log\LoggerInterface;

/**
 * Warms up cache for all customer user's who logged in during cache ttl time period.
 */
class OwnerTreeCacheJobProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    /**
     * @var JobRunner
     */
    private $jobRunner;

    /**
     * @var MessageProducerInterface
     */
    private $producer;

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var OwnershipMetadataProviderInterface
     */
    private $ownershipMetadataProvider;

    /**
     * @var BusinessUnitMessageFactory
     */
    private $businessUnitMessageFactory;

    /**
     * @var OwnerTreeMessageFactory
     */
    private $ownerTreeMessageFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        JobRunner $jobRunner,
        MessageProducerInterface $producer,
        ManagerRegistry $doctrine,
        OwnershipMetadataProviderInterface $ownershipMetadataProvider,
        BusinessUnitMessageFactory $businessUnitMessageFactory,
        OwnerTreeMessageFactory $ownerTreeMessageFactory,
        LoggerInterface $logger
    ) {
        $this->jobRunner = $jobRunner;
        $this->producer = $producer;
        $this->doctrine = $doctrine;
        $this->ownershipMetadataProvider = $ownershipMetadataProvider;
        $this->businessUnitMessageFactory = $businessUnitMessageFactory;
        $this->ownerTreeMessageFactory = $ownerTreeMessageFactory;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $data = JSON::decode($message->getBody());

        try {
            $cacheTtl = $this->ownerTreeMessageFactory->getCacheTtl($data);
            $result = $this->jobRunner->runUnique(
                $message->getMessageId(),
                Topics::CALCULATE_OWNER_TREE_CACHE,
                function (JobRunner $jobRunner) use ($cacheTtl) {
                    $userClass = $this->ownershipMetadataProvider->getUserClass();

                    /** @var QueryBuilder $queryBuilder */
                    $queryBuilder = $this->doctrine->getRepository($userClass)->createQueryBuilder('cu');

                    $dateTime = (new \DateTime())->sub(new \DateInterval(sprintf('PT%dS', $cacheTtl)));

                    $customerIds = $queryBuilder
                        ->distinct()
                        ->select('IDENTITY(cu.customer)')
                        ->andWhere($queryBuilder->expr()->gt('cu.lastLogin', ':dateTime', true))
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
        } catch (\Exception $e) {
            $this->logger->error(
                'Unexpected exception occurred during queue message processing',
                [
                    'topic' => Topics::CALCULATE_OWNER_TREE_CACHE,
                    'exception' => $e
                ]
            );

            return self::REJECT;
        }

        return $result ? self::ACK : self::REJECT;
    }

    private function scheduleCacheRecalculationForCustomer(JobRunner $jobRunner, string $entityClass, int $entityId)
    {
        $jobRunner->createDelayed(
            sprintf('%s:%s:%s', Topics::CALCULATE_OWNER_TREE_CACHE, $entityClass, $entityId),
            function (JobRunner $jobRunner, Job $child) use ($entityClass, $entityId) {
                $messageData = $this->businessUnitMessageFactory
                    ->createMessage($child->getId(), $entityClass, $entityId);

                $this->producer->send(Topics::CALCULATE_BUSINESS_UNIT_OWNER_TREE_CACHE, $messageData);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics()
    {
        return [Topics::CALCULATE_OWNER_TREE_CACHE];
    }
}
