<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\EventListener;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityBundle\Helper\FieldHelper;
use Oro\Bundle\ImportExportBundle\Event\LoadEntityRulesAndBackendHeadersEvent;
use Oro\Bundle\ImportExportBundle\EventListener\ImportExportHeaderModifier;

/**
 * Listener adds appropriate rules and headers for owner and customer relations for CustomerUser after normalization.
 * @see \Oro\Bundle\CustomerBundle\ImportExport\Serializer\Normalizer\CustomerUserNormalizer
 */
class CustomerUserHeadersListener
{
    private FieldHelper $fieldHelper;

    public function __construct(FieldHelper $fieldHelper)
    {
        $this->fieldHelper = $fieldHelper;
    }

    public function afterLoadEntityRulesAndBackendHeaders(LoadEntityRulesAndBackendHeadersEvent $event): void
    {
        if ($event->getEntityName() !== CustomerUser::class || !$event->isFullData()) {
            return;
        }

        if (!$this->fieldHelper->getConfigValue(CustomerUser::class, 'owner', 'excluded')) {
            ImportExportHeaderModifier::addHeader(
                $event,
                sprintf('owner%sid', $event->getConvertDelimiter()),
                'Owner Id',
                80
            );
        }

        if (!$this->fieldHelper->getConfigValue(CustomerUser::class, 'customer', 'excluded')) {
            ImportExportHeaderModifier::addHeader(
                $event,
                sprintf('customer%sname', $event->getConvertDelimiter()),
                'Customer Name',
                40
            );
        }
    }
}
