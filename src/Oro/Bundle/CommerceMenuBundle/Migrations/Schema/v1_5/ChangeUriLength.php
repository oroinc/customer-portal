<?php

namespace Oro\Bundle\CommerceMenuBundle\Migrations\Schema\v1_5;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class ChangeUriLength implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('oro_commerce_menu_upd');
        $table
            ->getColumn('uri')
            ->setType(Type::getType(Type::STRING))
            ->setOptions(['length' => 8190, 'notnull' => false]);
    }
}
