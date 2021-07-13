<?php

namespace Oro\Bundle\CustomerBundle\Async;

use Oro\Bundle\CustomerBundle\Model\BusinessUnitMessageFactory;
use Oro\Bundle\CustomerBundle\Owner\FrontendOwnerTreeProvider;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;

/**
 * Warms up cache for a given business unit entity by entityClass and entityId.
 */
class BusinessUnitOwnerTreeCacheJobProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    /**
     * @var JobRunner
     */
    private $jobRunner;

    /**
     * @var BusinessUnitMessageFactory
     */
    private $messageFactory;

    /**
     * @var FrontendOwnerTreeProvider
     */
    private $frontendOwnerTreeProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param JobRunner $jobRunner
     * @param FrontendOwnerTreeProvider $frontendOwnerTreeProvider
     * @param BusinessUnitMessageFactory $businessUnitMessageFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        JobRunner $jobRunner,
        FrontendOwnerTreeProvider $frontendOwnerTreeProvider,
        BusinessUnitMessageFactory $businessUnitMessageFactory,
        LoggerInterface $logger
    ) {
        $this->jobRunner = $jobRunner;
        $this->messageFactory = $businessUnitMessageFactory;
        $this->frontendOwnerTreeProvider = $frontendOwnerTreeProvider;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $data = JSON::decode($message->getBody());

        try {
            $jobId = $this->messageFactory->getJobIdFromMessage($data);
            $businessUnit = $this->messageFactory->getBusinessUnitFromMessage($data);
            $this->jobRunner->runDelayed($jobId, function () use ($businessUnit) {
                $this->frontendOwnerTreeProvider->getTreeByBusinessUnit($businessUnit);

                return true;
            });
        } catch (InvalidArgumentException $e) {
            $this->logger->error(
                'Queue Message is invalid',
                ['exception' => $e]
            );

            return self::REJECT;
        } catch (\Exception $exception) {
            $this->logger->error(
                'Unexpected exception occurred during queue message processing',
                [
                    'exception' => $exception,
                    'topic' => Topics::CALCULATE_BUSINESS_UNIT_OWNER_TREE_CACHE
                ]
            );

            return self::REJECT;
        }

        return self::ACK;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics()
    {
        return [Topics::CALCULATE_BUSINESS_UNIT_OWNER_TREE_CACHE];
    }
}
