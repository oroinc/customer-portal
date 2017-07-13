<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_15;

use Doctrine\DBAL\Schema\Schema;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCustomerBundle implements Migration, ContainerAwareInterface
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        // send migration message to queue. we should process this migration asynchronous because instances
        // could have a lot of customer user in system.
        $this->container->get('oro_message_queue.message_producer')
            ->send(ClearLostCustomerUsers::TOPIC_NAME, '');
    }
}
