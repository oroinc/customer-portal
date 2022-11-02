<?php

namespace Oro\Bundle\CustomerBundle\Async;

use Oro\Bundle\CustomerBundle\Async\Topic\CustomerCalculateOwnerTreeCacheByBusinessUnitTopic;
use Oro\Bundle\CustomerBundle\Model\BusinessUnitMessageFactory;
use Oro\Bundle\CustomerBundle\Owner\FrontendOwnerTreeProvider;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * Warms up cache for a given business unit entity by entityClass and entityId.
 */
class BusinessUnitOwnerTreeCacheJobProcessor implements
    MessageProcessorInterface,
    TopicSubscriberInterface,
    LoggerAwareInterface
{
    use LoggerAwareTrait;

    private JobRunner $jobRunner;

    private BusinessUnitMessageFactory $messageFactory;

    private FrontendOwnerTreeProvider $frontendOwnerTreeProvider;

    public function __construct(
        JobRunner $jobRunner,
        FrontendOwnerTreeProvider $frontendOwnerTreeProvider,
        BusinessUnitMessageFactory $businessUnitMessageFactory
    ) {
        $this->jobRunner = $jobRunner;
        $this->messageFactory = $businessUnitMessageFactory;
        $this->frontendOwnerTreeProvider = $frontendOwnerTreeProvider;

        $this->logger = new NullLogger();
    }

    public function process(MessageInterface $message, SessionInterface $session): string
    {
        $messageBody = $message->getBody();
        $jobId = $this->messageFactory->getJobIdFromMessage($messageBody);
        $businessUnit = $this->messageFactory->getBusinessUnitFromMessage($messageBody);
        if (!$businessUnit) {
            $this->logger->error('Business unit entity {entityClass} #{entityId} is not found', $messageBody);

            return self::REJECT;
        }

        $this->jobRunner->runDelayed($jobId, function () use ($businessUnit) {
            $this->frontendOwnerTreeProvider->getTreeByBusinessUnit($businessUnit);

            return true;
        });

        return self::ACK;
    }

    public static function getSubscribedTopics(): array
    {
        return [CustomerCalculateOwnerTreeCacheByBusinessUnitTopic::getName()];
    }
}
