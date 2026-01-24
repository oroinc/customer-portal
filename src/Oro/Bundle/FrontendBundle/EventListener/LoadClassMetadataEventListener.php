<?php

namespace Oro\Bundle\FrontendBundle\EventListener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\MappingException;
use Oro\Bundle\FrontendBundle\CacheWarmer\ClassMigration;

/**
 * Handles Doctrine ORM class metadata loading events and applies entity class migrations.
 *
 * This listener intercepts the LoadClassMetadata event to validate and update association mappings
 * when entity classes have been migrated. It uses the ClassMigration service to replace old
 * entity class names with their new equivalents in the ORM mapping configuration.
 */
class LoadClassMetadataEventListener
{
    /**
     * @var ClassMigration
     */
    private $classMigration;

    public function __construct(ClassMigration $classMigration)
    {
        $this->classMigration = $classMigration;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $eventArgs->getClassMetadata();
        try {
            $classMetadata->validateAssociations();
        } catch (MappingException $e) {
            foreach ($classMetadata->associationMappings as $name => $associationMapping) {
                if (array_key_exists('targetEntity', $associationMapping)) {
                    $classMetadata->associationMappings[$name]['targetEntity'] =
                        $this->classMigration->replaceStringValues($associationMapping['targetEntity']);
                }
            }
        }
    }
}
