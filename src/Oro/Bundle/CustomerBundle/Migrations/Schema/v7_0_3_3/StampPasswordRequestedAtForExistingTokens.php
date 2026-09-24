<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v7_0_3_3;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class StampPasswordRequestedAtForExistingTokens implements Migration
{
    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        $queries->addQuery(new ParametrizedSqlMigrationQuery(
            'UPDATE oro_customer_user SET password_requested = :now '
            . 'WHERE confirmation_token IS NOT NULL AND password_requested IS NULL AND confirmed = true',
            ['now' => new \DateTime('now', new \DateTimeZone('UTC'))],
            ['now' => Types::DATETIME_MUTABLE]
        ));
    }
}
