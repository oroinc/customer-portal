<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_31;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Adds auth status column to customer user table.
 */
class AddAuthStatusColumn implements Migration, ExtendExtensionAwareInterface
{
    private ExtendExtension $extendExtension;

    /**
     * {@inheritDoc}
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries)
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
        $options->set(
            'enum',
            'immutable_codes',
            [
                CustomerUserManager::STATUS_ACTIVE,
                CustomerUserManager::STATUS_RESET,
            ]
        );

        $enumTable->addOption(OroOptions::KEY, $options);

        $queries->addPostQuery(new InsertAuthStatusesQuery($this->extendExtension));
    }
}
