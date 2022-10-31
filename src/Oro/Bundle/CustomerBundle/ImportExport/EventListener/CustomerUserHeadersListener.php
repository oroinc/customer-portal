<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\EventListener;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityBundle\Helper\FieldHelper;
use Oro\Bundle\ImportExportBundle\Event\LoadEntityRulesAndBackendHeadersEvent;

/**
 * Listener adds appropriate rules and headers for owner and customer relations for CustomerUser after normalization.
 * @see \Oro\Bundle\CustomerBundle\ImportExport\Serializer\Normalizer\CustomerUserNormalizer
 */
class CustomerUserHeadersListener
{
    /**
     * @var FieldHelper
     */
    private $fieldHelper;

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
            $this->addHeader(
                $event,
                sprintf('owner%sid', $event->getConvertDelimiter()),
                'Owner Id',
                80
            );
        }

        if (!$this->fieldHelper->getConfigValue(CustomerUser::class, 'customer', 'excluded')) {
            $this->addHeader(
                $event,
                sprintf('customer%sname', $event->getConvertDelimiter()),
                'Customer Name',
                40
            );
        }
    }

    /**
     * @param LoadEntityRulesAndBackendHeadersEvent $event
     * @param string $value
     * @param string $label
     * @param int|null $order
     */
    private function addHeader(
        LoadEntityRulesAndBackendHeadersEvent $event,
        string $value,
        string $label,
        int $order = 20
    ): void {
        $exist = false;
        foreach ($event->getHeaders() as $header) {
            if ($header['value'] === $value) {
                $exist = true;
                break;
            }
        }

        if (!$exist) {
            $event->addHeader([
                'value' => $value,
                'order' => $order,
            ]);
            $event->setRule(
                $label,
                [
                    'value' => $value,
                    'order' => $order,
                ]
            );
        }
    }
}
