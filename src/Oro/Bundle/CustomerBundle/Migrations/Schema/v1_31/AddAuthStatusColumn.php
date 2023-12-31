<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_31;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareTrait;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\EntityExtendBundle\Migration\Query\EnumDataValue;
use Oro\Bundle\EntityExtendBundle\Migration\Query\InsertEnumValuesQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Adds auth status column to customer user table.
 */
class AddAuthStatusColumn implements Migration, ExtendExtensionAwareInterface
{
    use ExtendExtensionAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries): void
    {
        $enumTable = $this->extendExtension->addEnumField(
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

        $queries->addPostQuery(new InsertEnumValuesQuery($this->extendExtension, 'cu_auth_status', [
            new EnumDataValue(CustomerUserManager::STATUS_ACTIVE, 'Active', 1, true),
            new EnumDataValue(CustomerUserManager::STATUS_RESET, 'Reset', 2)
        ]));

        $queries->addPostQuery(new ParametrizedSqlMigrationQuery(
            'UPDATE oro_customer_user SET auth_status_id = :default_status',
            ['default_status' => CustomerUserManager::STATUS_ACTIVE],
            ['default_status' => Types::STRING]
        ));
    }
}
