<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_14;

use Doctrine\DBAL\Schema\Schema;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\MigrationBundle\Migration\Extension\DataStorageExtension;
use Oro\Bundle\MigrationBundle\Migration\Extension\DataStorageExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

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

    private function updateConfig()
    {
        $storage = $this->dataStorageExtension;
        $stateData = [];

        $dumper = $this->container->get('oro_entity_extend.tools.dumper');
        $dumper->updateConfig(function (ConfigInterface $config) use ($storage, $stateData) {
            $configId  = $config->getId();
            $className = $configId->getClassName();

            $isSupported = $className === CustomerUser::class || $className === CustomerUserRole::class;

            if ($isSupported) {
                $stateData = $storage->get('initial_entity_config_state', []);
                $stateData['entities'][$className] = $config->get('state');
            }

            return $isSupported;
        });

        $this->dataStorageExtension->set('initial_entity_config_state', $stateData);

        $configManager = $this->container->get('oro_entity_config.config_manager');
        $provider = $configManager->getProvider('extend');

        $entityConfig = $provider->getConfig(CustomerUser::class);
        $entityConfig->set('state', ExtendScope::STATE_ACTIVE);
        $configManager->persist($entityConfig);

        $entityConfig = $provider->getConfig(CustomerUserRole::class);
        $entityConfig->set('state', ExtendScope::STATE_ACTIVE);
        $configManager->persist($entityConfig);
    }
}
