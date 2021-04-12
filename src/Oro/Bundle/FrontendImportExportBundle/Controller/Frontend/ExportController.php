<?php

namespace Oro\Bundle\FrontendImportExportBundle\Controller\Frontend;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\FrontendImportExportBundle\Async\Topics;
use Oro\Bundle\FrontendImportExportBundle\Entity\FrontendImportExportResult;
use Oro\Bundle\FrontendImportExportBundle\Handler\FrontendExportHandler;
use Oro\Bundle\ImportExportBundle\Async\ImportExportResultSummarizer;
use Oro\Bundle\ImportExportBundle\Exception\ImportExportExpiredException;
use Oro\Bundle\ImportExportBundle\Exception\InvalidArgumentException;
use Oro\Bundle\ImportExportBundle\Job\JobExecutor;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\PricingBundle\Manager\UserCurrencyManager;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\CsrfProtection;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Handles export requests from the storefront.
 */
class ExportController extends AbstractController
{
    /**
     * @Route("/{processorAlias}", name="oro_frontend_importexport_export", methods={"POST"})
     * @CsrfProtection()
     *
     */
    public function exportAction(string $processorAlias, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $user = $this->getUser();
        /** @var Website $currentWebsite */
        $currentWebsite = $this->get(WebsiteManager::class)->getCurrentWebsite();
        $currentLocalization = $this->get(LocalizationHelper::class)->getCurrentLocalization();
        $localizationId = $currentLocalization ? $currentLocalization->getId() : null;
        $currentCurrency = $this->get(UserCurrencyManager::class)->getUserCurrency($currentWebsite);
        $refererUrl = $this->getRefererUrl($request, $currentWebsite);

        $this->get(MessageProducerInterface::class)->send(Topics::PRE_EXPORT, [
            'jobName' => $request->get('exportJob', JobExecutor::JOB_EXPORT_TO_CSV),
            'processorAlias' => $processorAlias,
            'outputFilePrefix' => $request->get('filePrefix'),
            'refererUrl' => $refererUrl,
            'options' => array_merge($this->getOptionsFromRequest($request), [
                'currentLocalizationId' => $localizationId,
                'currentCurrency' => $currentCurrency
            ]),
            'userId' => $user instanceof CustomerUser ? $user->getId() : null,
            'websiteId' => $currentWebsite ? $currentWebsite->getId() : null,
        ]);

        return new JsonResponse(['success' => true]);
    }

    /**
     * @Route("/download/{jobId}", name="oro_frontend_importexport_export_download", requirements={"jobId"="\d+"})
     * @ParamConverter("result", options={"mapping": {"jobId": "jobId"}})
     * @Acl(
     *      id="oro_frontendimportexport_result_view",
     *      type="entity",
     *      class="OroFrontendImportExportBundle:FrontendImportExportResult",
     *      permission="VIEW"
     * )
     *
     * @param FrontendImportExportResult $result
     *
     * @return Response
     */
    public function downloadExportResultAction(FrontendImportExportResult $result): Response
    {
        if ($result->isExpired()) {
            throw new ImportExportExpiredException();
        }

        return $this->get(FrontendExportHandler::class)->handleDownloadExportResult($result->getFilename());
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getOptionsFromRequest(Request $request): array
    {
        $options = $request->get('options', []);

        if (!is_array($options)) {
            throw new BadRequestHttpException('Request parameter "options" must be array.');
        }

        return $options;
    }

    /**
     * @param Request $request
     * @param Website $website
     * @return string
     */
    private function getRefererUrl(Request $request, Website $website): string
    {
        $referer = $request->headers->get('referer');
        $baseUrl = $this->get(WebsiteUrlResolver::class)->getWebsiteUrl($website, true);

        return str_replace($baseUrl, '', $referer);
    }

    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                WebsiteManager::class,
                MessageProducerInterface::class,
                LocalizationHelper::class,
                UserCurrencyManager::class,
                ImportExportResultSummarizer::class,
                FrontendExportHandler::class,
                WebsiteUrlResolver::class
            ]
        );
    }
}
