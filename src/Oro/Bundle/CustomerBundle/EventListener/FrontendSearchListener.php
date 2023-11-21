<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Oro\Bundle\SearchBundle\Event\PrepareEntityMapEvent;
use Oro\Bundle\SearchBundle\Event\SearchMappingCollectEvent;
use Oro\Bundle\SecurityBundle\EventListener\SearchListener;

/**
 * Adds fields configuration to the search entity map based on frontend ownership metadata.
 */
class FrontendSearchListener extends SearchListener
{
    /**
     * {@inheritDoc}
     */
    public function collectEntityMapEvent(SearchMappingCollectEvent $event): void
    {
        $mapConfig = $event->getMappingConfig();
        foreach ($mapConfig as $className => $mapping) {
            $metadata = $this->metadataProvider->getMetadata($className);

            $fieldName = $metadata->getOwnerFieldName();
            if (!$fieldName) {
                continue;
            }

            $mapConfig[$className]['fields'][] = [
                'name'          => $fieldName,
                'target_type'   => 'integer',
                'target_fields' => [$this->getOwnerKey($metadata, $mapping['alias'])]
            ];
        }

        $event->setMappingConfig($mapConfig);
    }

    /**
     * {@inheritDoc}
     */
    public function prepareEntityMapEvent(PrepareEntityMapEvent $event): void
    {
        $metadata = $this->metadataProvider->getMetadata($event->getClassName());
        $ownerId = $this->getOwnerId($metadata, $event->getEntity());
        if (!$ownerId) {
            return;
        }

        $data = $event->getData();

        $data['integer'][$this->getOwnerKey($metadata, $event->getEntityMapping()['alias'])] = $ownerId;

        $event->setData($data);
    }
}
