<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\Strategy;

use Doctrine\ORM\PersistentCollection;

use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;

class CustomerImportStrategy extends ConfigurableAddOrReplaceStrategy
{
    /**
     * {@inheritdoc}
     */
    protected function findExistingEntity($entity, array $searchContext = [])
    {
        $existingEntity = parent::findExistingEntity($entity, $searchContext);

        // we need to initialize customer groups because of issue with UoW which tries to load old data
        // from database and overrides customer groups from imported file
        if ($existingEntity instanceof CustomerGroup) {
            $customers = $existingEntity->getCustomers();
            if ($customers instanceof PersistentCollection) {
                $customers->initialize();
            }
        }

        return $existingEntity;
    }
}
