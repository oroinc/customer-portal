<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_18;

use Doctrine\DBAL\Schema\Schema;
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
     * {@inheritDoc}
     */
    public function getOrder(): int
    {
        return 10;
    }

    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries): void
    {
        $table = $schema->getTable('oro_customer_user');
        if (!$table->hasIndex('idx_oro_customer_user_email')) {
            $table->addIndex(['email'], 'idx_oro_customer_user_email');
        }

        if ($table->hasColumn('email_lowercase')) {
            return;
        }

        $table->addColumn('email_lowercase', 'string', ['length' => 255, 'notnull' => false]);
        $table->addIndex(['email_lowercase'], 'idx_oro_customer_user_email_lowercase');

        // Fill email_lowercase column with lowercase emails.
        $queries->addPostQuery(new SqlMigrationQuery('UPDATE oro_customer_user SET email_lowercase = LOWER(email)'));
        $queries->addPostQuery(new EnableCaseInsensitiveEmailConfigQuery());
    }
}
