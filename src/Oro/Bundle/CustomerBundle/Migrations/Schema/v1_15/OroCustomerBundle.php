<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_15;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
        /** Tables generation **/
        $this->updateCustomerVisitorTable($schema);

        /** Tables modifications **/
        $this->updateCustomerUserTable($schema);
    }

    /**
     * Update oro_customer_visitor table
     */
    protected function updateCustomerVisitorTable(Schema $schema)
    {
        $table = $schema->getTable('oro_customer_visitor');
        $table->addColumn('customer_user_id', 'integer', ['notnull' => false]);
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer_user'),
            ['customer_user_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $table->addUniqueIndex(['customer_user_id'], 'idx_customer_visitor_id_customer_user_id');
    }

    /**
     * Update oro_customer_user table
     */
    private function updateCustomerUserTable(Schema $schema)
    {
        $table = $schema->getTable('oro_customer_user');
        $table->addColumn('is_guest', 'boolean', ['default' => false]);

        //remove uniq indices for name and email fields
        $table->dropIndex('UNIQ_9511CEB5F85E0677');
        $table->dropIndex('uniq_oro_customer_user_email');
    }
}
