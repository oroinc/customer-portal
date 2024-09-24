<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_31;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\OutdatedExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\OutdatedExtendExtensionAwareTrait;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\EntityExtendBundle\Migration\Query\OutdatedEnumDataValue;
use Oro\Bundle\EntityExtendBundle\Migration\Query\OutdatedInsertEnumValuesQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Adds auth status column to customer user table.
 */
class AddAuthStatusColumn implements Migration, OutdatedExtendExtensionAwareInterface
{
    use OutdatedExtendExtensionAwareTrait;

    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        $enumTable = $this->outdatedExtendExtension->addOutdatedEnumField(
            $schema,
            'oro_customer_user',
            'auth_status',
            'cu_auth_status',
            false,
            false,
            [
                'attribute' => ['searchable' => false, 'filterable' => true],
                'importexport' => ['excluded' => true]
            ]
        );

        $options = new OroOptions();
        $options->set('enum', 'immutable_codes', [
            CustomerUserManager::STATUS_ACTIVE,
            CustomerUserManager::STATUS_RESET
        ]);
        $enumTable->addOption(OroOptions::KEY, $options);

        $queries->addPostQuery(new OutdatedInsertEnumValuesQuery($this->outdatedExtendExtension, 'cu_auth_status', [
            new OutdatedEnumDataValue(CustomerUserManager::STATUS_ACTIVE, 'Active', 1, true),
            new OutdatedEnumDataValue(CustomerUserManager::STATUS_RESET, 'Reset', 2)
        ]));

        $queries->addPostQuery(new ParametrizedSqlMigrationQuery(
            'UPDATE oro_customer_user SET auth_status_id = :default_status',
            ['default_status' => CustomerUserManager::STATUS_ACTIVE],
            ['default_status' => Types::STRING]
        ));
    }
}
