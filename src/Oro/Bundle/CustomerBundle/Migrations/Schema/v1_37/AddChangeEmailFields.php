<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_37;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareTrait;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddChangeEmailFields implements Migration, ExtendExtensionAwareInterface
{
    use ExtendExtensionAwareTrait;

    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        $table = $schema->getTable('oro_customer_user');
        if (!$table->hasColumn('new_email')) {
            $table->addColumn('new_email', 'string', ['notnull' => false, 'length' => 255]);
        }
        if (!$table->hasColumn('new_email_verification_code')) {
            $table->addColumn('new_email_verification_code', 'string', ['notnull' => false, 'length' => 255]);
        }
        if (!$table->hasColumn('email_verification_code_requested_at')) {
            $table->addColumn('email_verification_code_requested_at', 'datetime', ['notnull' => false]);
        }
    }
}
