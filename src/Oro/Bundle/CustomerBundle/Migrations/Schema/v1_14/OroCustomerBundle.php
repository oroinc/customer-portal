<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_14;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Extension\DataStorageExtension;
use Oro\Bundle\MigrationBundle\Migration\Extension\DataStorageExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class OroCustomerBundle implements
    Migration,
    ContainerAwareInterface,
    DataStorageExtensionAwareInterface
{
    use ContainerAwareTrait;

    /** @var DataStorageExtension */
    private $dataStorageExtension;

    /**
     * {@inheritdoc}
     */
    public function setDataStorageExtension(DataStorageExtension $dataStorageExtension)
    {
        $this->dataStorageExtension = $dataStorageExtension;
    }

    /**
     * @inheritDoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createCustomerVisitorTable($schema);

        // send migration message to queue. we should process this migration asynchronous because instances
        // could have a lot of customer user in system.
        $this->container->get('oro_message_queue.message_producer')->send(ClearLostCustomerUsersTopic::getName(), '');
    }

    /**
     * Create oro_customer_visitor table
     */
    protected function createCustomerVisitorTable(Schema $schema)
    {
        $table = $schema->createTable('oro_customer_visitor');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('last_visit', 'datetime', []);
        $table->addColumn('session_id', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['id', 'session_id'], 'id_session_id_idx');
    }
}
