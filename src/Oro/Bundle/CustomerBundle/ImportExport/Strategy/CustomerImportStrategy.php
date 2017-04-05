<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\Strategy;

use Doctrine\ORM\PersistentCollection;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;

class CustomerImportStrategy extends ConfigurableAddOrReplaceStrategy
{
    /**
     * {@inheritdoc}
     */
    protected function beforeProcessEntity($entity)
    {
        $entity = parent::beforeProcessEntity($entity);
        $entity = $this->verifyIfParentExists($entity);

        return $entity;
    }

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

    /**
     * @param object $entity
     * @return object|null
     */
    private function verifyIfParentExists($entity)
    {
        if ($entity instanceof Customer && $entity->getParent()) {
            $parent = $this->findExistingEntityByIdentityFields($entity->getParent());

            if ($parent === null) {
                $this->context->addPostponedRow($this->context->getValue('rawItemData'));

                return null;
            }
        }

        return $entity;
    }
}
