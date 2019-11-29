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
     * {@inheritdoc}
     */
    public function collectEntityMapEvent(SearchMappingCollectEvent $event)
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
     * {@inheritdoc}
     */
    public function prepareEntityMapEvent(PrepareEntityMapEvent $event)
    {
        $metadata = $this->metadataProvider->getMetadata($event->getClassName());
        if (!$metadata) {
            return;
        }

        $ownerId = $this->getOwnerId($metadata, $event->getEntity());
        if (!$ownerId) {
            return;
        }

        $data = $event->getData();

        $data['integer'][$this->getOwnerKey($metadata, $event->getEntityMapping()['alias'])] = $ownerId;

        $event->setData($data);
    }
}
