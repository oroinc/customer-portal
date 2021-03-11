<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\Writer;

use Oro\Bundle\CustomerBundle\ImportExport\Strategy\CustomerUserAddOrReplaceStrategy;
use Oro\Bundle\IntegrationBundle\ImportExport\Writer\PersistentBatchWriter;

/**
 * Write Customer User entities.
 * Clear emails saved for in-batch uniqueness checks.
 */
class CustomerUserWriter extends PersistentBatchWriter
{
    public function write(array $items)
    {
        $this->contextRegistry
            ->getByStepExecution($this->stepExecution)
            ->setValue(CustomerUserAddOrReplaceStrategy::PROCESSED_EMAILS, null);

        parent::write($items);
    }
}
