<?php

namespace Oro\Bundle\WebsiteBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class SetNotNullableTitleColumn implements Migration, OrderedMigrationInterface
{
    #[\Override]
    public function getOrder()
    {
        return 30;
    }

    #[\Override]
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orob2b_locale');
        $table
            ->getColumn('title')
            ->setType(Type::getType(Types::STRING))
            ->setOptions(['length' => 255, 'notnull' => true]);

        $table->addUniqueIndex(['title']);
    }
}
