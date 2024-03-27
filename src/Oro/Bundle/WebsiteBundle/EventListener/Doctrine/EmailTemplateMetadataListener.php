<?php

namespace Oro\Bundle\WebsiteBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Oro\Bundle\EmailBundle\Entity\EmailTemplate;

/**
 * Adds 'website_id' to the UQ_NAME unique constraint of the {@see EmailTemplate} entity class.
 */
class EmailTemplateMetadataListener
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $event): void
    {
        $classMetadata = $event->getClassMetadata();
        if ($classMetadata->getName() !== 'Oro\Bundle\EmailBundle\Entity\EmailTemplate') {
            return;
        }

        $classMetadata->table['uniqueConstraints']['UQ_NAME'] = ['columns' => ['name', 'entityName', 'website_id']];
    }
}
