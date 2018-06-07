<?php

namespace Oro\Bundle\FrontendBundle\Async;

use Oro\Bundle\MessageQueueBundle\Entity\Job;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;

class ParentMessageProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    /** @var MessageProducerInterface */
    private $producer;

    /** @var JobRunner */
    private $jobRunner;

    /**
     * @param MessageProducerInterface $producer
     * @param JobRunner $jobRunner
     */
    public function __construct(MessageProducerInterface $producer, JobRunner $jobRunner)
    {
        $this->producer = $producer;
        $this->jobRunner = $jobRunner;
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $result = $this->jobRunner->runUnique(
            $message->getMessageId(),
            'parent_unique',
            function (JobRunner $jobRunner) {
                foreach (range(0, 100000) as $num) {
                    $jobRunner->createDelayed(
                        'child_job_' . $num,
                        function (JobRunner $jobRunner, Job $child) {
                            $this->producer->send(Topics::CHILD_MESSAGE_TOPIC, ['jobId' => $child->getId()]);
                        }
                    );
                }

                return true;
            }
        );

        return $result ? self::ACK : self::REJECT;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics()
    {
        return [Topics::PARENT_MESSAGE_TOPIC];
    }
}
