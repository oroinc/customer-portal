<?php

namespace Oro\Bundle\FrontendImportExportBundle\Controller\Frontend;

use Oro\Bundle\FrontendImportExportBundle\Entity\FrontendImportExportResult;
use Oro\Bundle\FrontendImportExportBundle\Handler\FrontendExportHandler;
use Oro\Bundle\ImportExportBundle\Async\ImportExportResultSummarizer;
use Oro\Bundle\ImportExportBundle\Exception\ImportExportExpiredException;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Handles downloading export files.
 */
class ExportController extends AbstractController
{
    #[Route(
        path: '/download/{jobId}',
        name: 'oro_frontend_importexport_export_download',
        requirements: ['jobId' => '\d+']
    )]
    #[ParamConverter('result', options: ['mapping' => ['jobId' => 'jobId']])]
    #[Acl(
        id: 'oro_frontendimportexport_result_view',
        type: 'entity',
        class: FrontendImportExportResult::class,
        permission: 'VIEW'
    )]
    public function downloadExportResultAction(FrontendImportExportResult $result): Response
    {
        if ($result->isExpired()) {
            throw new ImportExportExpiredException();
        }

        return $this->container->get(FrontendExportHandler::class)->handleDownloadExportResult($result->getFilename());
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                ImportExportResultSummarizer::class,
                FrontendExportHandler::class
            ]
        );
    }
}
