<?php

namespace Oro\Bundle\FrontendImportExportBundle\Controller\Frontend;

use Oro\Bundle\FrontendImportExportBundle\Entity\FrontendImportExportResult;
use Oro\Bundle\FrontendImportExportBundle\Handler\FrontendExportHandler;
use Oro\Bundle\ImportExportBundle\Async\ImportExportResultSummarizer;
use Oro\Bundle\ImportExportBundle\Exception\ImportExportExpiredException;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Handles downloading export files.
 */
class ExportController extends AbstractController
{
    /**
     * @Route("/download/{jobId}", name="oro_frontend_importexport_export_download", requirements={"jobId"="\d+"})
     * @ParamConverter("result", options={"mapping": {"jobId": "jobId"}})
     * @Acl(
     *      id="oro_frontendimportexport_result_view",
     *      type="entity",
     *      class="OroFrontendImportExportBundle:FrontendImportExportResult",
     *      permission="VIEW"
     * )
     */
    public function downloadExportResultAction(FrontendImportExportResult $result): Response
    {
        if ($result->isExpired()) {
            throw new ImportExportExpiredException();
        }

        return $this->get(FrontendExportHandler::class)->handleDownloadExportResult($result->getFilename());
    }

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
