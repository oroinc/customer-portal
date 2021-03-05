<?php

namespace Oro\Bundle\FrontendImportExportBundle\Async\Export;

use Oro\Bundle\FrontendImportExportBundle\Async\Topics;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;

/**
 * Class responsible for running the main export job.
 */
class PreExportMessageProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    public function process(MessageInterface $message, SessionInterface $session): string
    {
        return self::ACK;
    }

    public static function getSubscribedTopics(): array
    {
        return [Topics::PRE_EXPORT];
    }
}
