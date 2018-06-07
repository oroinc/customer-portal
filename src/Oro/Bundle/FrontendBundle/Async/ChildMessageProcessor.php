<?php

namespace Oro\Bundle\FrontendBundle\Async;

use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;

class ChildMessageProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    /** @var JobRunner */
    private $jobRunner;

    /**
     * @param JobRunner $jobRunner
     */
    public function __construct(JobRunner $jobRunner)
    {
        $this->jobRunner = $jobRunner;
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $data = JSON::decode($message->getBody());

        $result = $this->jobRunner->runDelayed($data['jobId'], function () {
            return true;
        });

        return $result ? self::ACK : self::REJECT;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics()
    {
        return [Topics::CHILD_MESSAGE_TOPIC];
    }
}
