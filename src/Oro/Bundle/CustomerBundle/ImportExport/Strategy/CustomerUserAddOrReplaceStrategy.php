<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\Strategy;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;

class CustomerUserAddOrReplaceStrategy extends ConfigurableAddOrReplaceStrategy
{
    /**
     * {@inheritdoc}
     * @param CustomerUser $entity
     */
    public function validateAndUpdateContext($entity)
    {
        $validationErrors = $this->strategyHelper->validateEntity($entity, ['Default', 'import']);
        if ($validationErrors) {
            $this->processValidationErrors($entity, $validationErrors);
            return null;
        }

        $this->updateContextCounters($entity);

        return $entity;
    }
}
