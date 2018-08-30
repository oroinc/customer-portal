<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_18;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\CustomerBundle\Migrations\Schema\OroCustomerBundleInstaller;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\SqlMigrationQuery;

/**
 * This migration adds new field 'emailLowercase' to CustomerUser entity
 * which is required for case insensitive email validation.
 */
class AddEmailLowercaseField implements Migration, OrderedMigrationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 10;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable(OroCustomerBundleInstaller::ORO_CUSTOMER_USER_TABLE_NAME);
        if (!$table->hasIndex('idx_oro_customer_user_email')) {
            $table->addIndex(['email'], 'idx_oro_customer_user_email', []);
        }

        if ($table->hasColumn('email_lowercase')) {
            return;
        }

        $table->addColumn('email_lowercase', 'string', ['length' => 255, 'notnull' => false]);
        $table->addIndex(['email_lowercase'], 'idx_oro_customer_user_email_lowercase', []);

        // Fill email_lowercase column with lowercase emails.
        $queries->addPostQuery(
            new SqlMigrationQuery(
                sprintf(
                    'UPDATE %s SET email_lowercase = LOWER(email)',
                    OroCustomerBundleInstaller::ORO_CUSTOMER_USER_TABLE_NAME
                )
            )
        );
        $queries->addPostQuery(new EnableCaseInsensitiveEmailConfigQuery());
    }
}
