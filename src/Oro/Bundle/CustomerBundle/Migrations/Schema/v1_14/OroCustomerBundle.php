<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_14;

use Doctrine\DBAL\Schema\Schema;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCustomerBundle implements Migration, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createCustomerVisitorTable($schema);
        $this->updateConfig();
    }

    /**
     * Create oro_customer_visitor table
     *
     * @param Schema $schema
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

    public function updateConfig()
    {
        $dumper = $this->container->get('oro_entity_extend.tools.dumper');
        $dumper->updateConfig(function (ConfigInterface $config) {
            $configId  = $config->getId();
            $className = $configId->getClassName();

            return $className === CustomerUser::class || $className === CustomerUserRole::class;
        });
    }
}
