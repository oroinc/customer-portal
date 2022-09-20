<?php

namespace Oro\Bundle\FrontendImportExportBundle\Async\Topic;

use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Topic for processing storefront export results before they are stored.
 */
class SaveExportResultTopic extends AbstractTopic
{
    public static function getName(): string
    {
        return 'oro_frontend_importexport.save_import_export_result';
    }

    public static function getDescription(): string
    {
        return 'Processes storefront export results before they are stored';
    }

    public function configureMessageBody(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined([
                'jobId',
                'entity',
                'type',
                'customerUserId',
                'options',
            ])
            ->setRequired([
                'jobId',
                'entity',
                'type',
                'customerUserId',
            ])
            ->setDefaults([
                'options' => [],
            ])
            ->addAllowedTypes('jobId', 'int')
            ->addAllowedTypes('entity', 'string')
            ->addAllowedTypes('type', 'string')
            ->addAllowedTypes('customerUserId', 'int')
            ->addAllowedTypes('options', 'array')
            ->addAllowedValues(
                'type',
                [
                    ProcessorRegistry::TYPE_EXPORT,
                ]
            );
    }
}
