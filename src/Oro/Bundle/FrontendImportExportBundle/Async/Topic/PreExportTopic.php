<?php

namespace Oro\Bundle\FrontendImportExportBundle\Async\Topic;

use Oro\Bundle\ImportExportBundle\Async\Topic\PreExportTopic as BasePreExportTopic;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Topic for generating a list of records for storefront export which are later used in child job.
 */
class PreExportTopic extends BasePreExportTopic
{
    #[\Override]
    public static function getName(): string
    {
        return 'oro_frontend_importexport.pre_export';
    }

    #[\Override]
    public static function getDescription(): string
    {
        return 'Generates a list of records for storefront export which are later used in child job';
    }

    #[\Override]
    public function configureMessageBody(OptionsResolver $resolver): void
    {
        parent::configureMessageBody($resolver);

        $resolver
            ->setDefined([
                'refererUrl'
            ])
            ->setDefaults([
                'refererUrl' => null,
            ])
            ->addAllowedTypes('refererUrl', ['string', 'null']);
    }

    #[\Override]
    public function createJobName($messageBody): string
    {
        return sprintf(
            'oro_frontend_importexport.pre_export.%s.user_%s',
            $messageBody['jobName'],
            $this->getUser()->getId()
        );
    }
}
