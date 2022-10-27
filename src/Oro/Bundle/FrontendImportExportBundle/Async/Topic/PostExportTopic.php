<?php

namespace Oro\Bundle\FrontendImportExportBundle\Async\Topic;

use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Topic for storefront export process finalization.
 */
class PostExportTopic extends AbstractTopic
{
    public static function getName(): string
    {
        return 'oro_frontend_importexport.post_export';
    }

    public static function getDescription(): string
    {
        return 'Finalizes storefront export process';
    }

    public function configureMessageBody(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined([
                'jobId',
                'jobName',
                'exportType',
                'outputFormat',
                'customerUserId',
                'entity',
                'refererUrl'
            ])
            ->setRequired([
                'jobId',
                'jobName',
                'exportType',
                'outputFormat',
                'customerUserId',
                'entity',
            ])
            ->setDefaults([
                'refererUrl' => null,
            ])
            ->addAllowedTypes('jobId', 'int')
            ->addAllowedTypes('jobName', 'string')
            ->addAllowedTypes('exportType', 'string')
            ->addAllowedTypes('outputFormat', 'string')
            ->addAllowedTypes('customerUserId', 'int')
            ->addAllowedTypes('entity', 'string')
            ->addAllowedTypes('refererUrl', ['string', 'null']);
    }
}
