<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\EventListener;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\EntityBundle\Helper\FieldHelper;
use Oro\Bundle\ImportExportBundle\Event\LoadEntityRulesAndBackendHeadersEvent;
use Oro\Bundle\ImportExportBundle\EventListener\ImportExportHeaderModifier;

/**
 * Listener adds appropriate rules and headers for owner and parent relations for Customer after normalization.
 * @see \Oro\Bundle\CustomerBundle\ImportExport\Serializer\Normalizer\CustomerNormalizer
 */
class CustomerHeadersListener
{
    private FieldHelper $fieldHelper;

    public function __construct(FieldHelper $fieldHelper)
    {
        $this->fieldHelper = $fieldHelper;
    }

    public function afterLoadEntityRulesAndBackendHeaders(LoadEntityRulesAndBackendHeadersEvent $event): void
    {
        if ($event->getEntityName() !== Customer::class || !$event->isFullData()) {
            return;
        }

        if (!$this->fieldHelper->getConfigValue(Customer::class, 'owner', 'excluded')) {
            ImportExportHeaderModifier::addHeader(
                $event,
                sprintf('owner%sid', $event->getConvertDelimiter()),
                'Owner Id',
                50
            );
        }

        if (!$this->fieldHelper->getConfigValue(Customer::class, 'parent', 'excluded')) {
            ImportExportHeaderModifier::addHeader(
                $event,
                sprintf('parent%sid', $event->getConvertDelimiter()),
                'Parent Id',
                30
            );
        }
    }
}
