<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_19;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\CustomerBundle\DependencyInjection\Configuration;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Remove obsolete config option value
 */
class RemoveSendPasswordWelcomeEmailConfigValue implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->removeFromConfig($queries, 'send_password_in_welcome_email');
    }

    /**
     * @param QueryBag $queries
     * @param string $name
     */
    protected function removeFromConfig(QueryBag $queries, $name)
    {
        $queries->addQuery(new ParametrizedSqlMigrationQuery(
            'DELETE FROM oro_config_value WHERE name = :name AND section = :section',
            ['name' => $name, 'section' => Configuration::ROOT_NODE]
        ));
    }
}
