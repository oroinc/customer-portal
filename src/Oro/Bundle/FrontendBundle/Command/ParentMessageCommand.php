<?php

namespace Oro\Bundle\FrontendBundle\Command;

use Oro\Bundle\FrontendBundle\Async\Topics;
use Oro\Component\MessageQueue\Client\Message;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ParentMessageCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('demo:message_queue_examples:parent');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $message = new Message();

        $producer = $this->getContainer()->get('oro_message_queue.client.message_producer');

        $producer->send(Topics::PARENT_MESSAGE_TOPIC, $message);
    }
}
