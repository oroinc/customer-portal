<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_18;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerGroupSelectType;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerSelectType;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserSelectType;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigEntityValueQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class UpdateEntitiesFormTypes implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addPostQuery($this->createUpdateEntityQuery(Customer::class, CustomerSelectType::class));
        $queries->addPostQuery($this->createUpdateEntityQuery(CustomerGroup::class, CustomerGroupSelectType::class));
        $queries->addPostQuery($this->createUpdateEntityQuery(CustomerUser::class, CustomerUserSelectType::class));
    }

    /**
     * @param string $entityClass
     * @param string $formType
     * @return UpdateEntityConfigEntityValueQuery
     */
    private function createUpdateEntityQuery(string $entityClass, string $formType): UpdateEntityConfigEntityValueQuery
    {
        return new UpdateEntityConfigEntityValueQuery(
            $entityClass,
            'form',
            'form_type',
            $formType
        );
    }
}
