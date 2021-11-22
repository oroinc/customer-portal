<?php

namespace Oro\Bundle\FrontendImportExportBundle\Tests\Unit\Async\Export;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Tests\Unit\Stub\CustomerUserStub;
use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Bundle\FrontendImportExportBundle\Async\Export\FrontendExportResultSummarizer;
use Oro\Bundle\FrontendImportExportBundle\Entity\FrontendImportExportResult;
use Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver;
use Oro\Bundle\WebsiteBundle\Tests\Unit\Stub\WebsiteStub;
use Oro\Component\MessageQueue\Job\Job;
use Oro\Component\MessageQueue\Job\JobProcessor;
use PHPUnit\Framework\TestCase;

class FrontendExportResultSummarizerTest extends TestCase
{
    private const JOB_ID = 142;
    private const ROOT_JOB_ID = 42;
    private const ROOT_JOB_NAME = 'sample.root_job.name';
    private const REFERER_URL = 'sample/url';

    /** @var WebsiteUrlResolver|\PHPUnit\Framework\MockObject\MockObject */
    private $websiteUrlResolver;

    /** @var FrontendExportResultSummarizer */
    private $resultSummarizer;

    protected function setUp(): void
    {
        $this->websiteUrlResolver = $this->createMock(WebsiteUrlResolver::class);

        $rootJob = new Job();
        $rootJob->setName(self::ROOT_JOB_NAME);
        $rootJob->setData(['refererUrl' => self::REFERER_URL]);
        $rootJob->setId(self::ROOT_JOB_ID);
        $rootJob->setId(self::ROOT_JOB_ID);

        $job = new Job();
        $job->setId(self::JOB_ID);
        $job->setRootJob($rootJob);

        $childJob1 = new Job();
        $childJob1->setData(['success' => 11, 'entities' => 'sample1']);
        $emptyChildJob = new Job();
        $childJob3 = new Job();
        $childJob3->setData(['success' => 22, 'entities' => 'sample2']);

        $rootJob->addChildJob($childJob1);
        $rootJob->addChildJob($emptyChildJob);
        $rootJob->addChildJob($childJob3);

        $jobProcessor = $this->createMock(JobProcessor::class);
        $jobProcessor->expects(self::any())
            ->method('findJobById')
            ->willReturnMap([[self::JOB_ID, $job], [self::ROOT_JOB_ID, $rootJob]]);

        $entityNameResolver = $this->createMock(EntityNameResolver::class);
        $entityNameResolver->expects(self::any())
            ->method('getName')
            ->willReturnCallback(static fn (CustomerUser $customerUser) => $customerUser->getFullName());

        $this->resultSummarizer = new FrontendExportResultSummarizer(
            $this->websiteUrlResolver,
            $jobProcessor,
            $entityNameResolver
        );
    }

    /**
     * @dataProvider getSummaryFromImportExportResultDataProvider
     */
    public function testGetSummaryFromImportExportResult(
        FrontendImportExportResult $importExportResult,
        array $expected
    ): void {
        $this->websiteUrlResolver->expects(self::any())
            ->method('getWebsiteSecurePath')
            ->willReturnMap(
                [
                    [
                        'oro_frontend_importexport_export_download',
                        ['jobId' => self::ROOT_JOB_ID],
                        $importExportResult->getCustomerUser()?->getWebsite(),
                        'https://example.org/download/' . self::ROOT_JOB_ID,
                    ],
                    [
                        'oro_frontend_root',
                        [],
                        $importExportResult->getCustomerUser()?->getWebsite(),
                        'https://example.org/',
                    ],
                ]
            );

        $this->websiteUrlResolver->expects(self::any())
            ->method('getWebsiteUrl')
            ->with($importExportResult->getCustomerUser()?->getWebsite(), true)
            ->willReturn('https://example.org/');

        $summary = $this->resultSummarizer->getSummaryFromImportExportResult($importExportResult);

        self::assertEquals($expected, $summary);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getSummaryFromImportExportResultDataProvider(): array
    {
        $blankImportExportResult = new FrontendImportExportResult();

        $importExportResultWithFilename = new FrontendImportExportResult();
        $importExportResultWithFilename->setFilename('sample_filename.csv');

        $customerUser = (new CustomerUserStub())->setFirstName('Test')->setLastName('Customer');

        $importExportResultWithCustomerUser = new FrontendImportExportResult();
        $importExportResultWithCustomerUser->setCustomerUser(clone $customerUser);

        $website = (new WebsiteStub())->setName('Test website');
        $customerUserWithWebsite = clone $customerUser;
        $customerUserWithWebsite->setWebsite($website);

        $importExportResultWithWebsite = new FrontendImportExportResult();
        $importExportResultWithWebsite->setCustomerUser($customerUserWithWebsite);

        $importExportResultWithJob = new FrontendImportExportResult();
        $importExportResultWithJob->setCustomerUser($customerUserWithWebsite);
        $importExportResultWithJob->setJobId(self::JOB_ID);

        $importExportResultWithRootJob = new FrontendImportExportResult();
        $importExportResultWithRootJob->setCustomerUser($customerUserWithWebsite);
        $importExportResultWithRootJob->setJobId(self::ROOT_JOB_ID);

        return [
            [
                '$importExportResult' => $blankImportExportResult,
                '$expected' => [
                    'exportResult' => [
                        'entities' => null,
                        'success' => false,
                        'fileName' => '',
                        'url' => '',
                        'user' => '',
                        'tryAgainUrl' => '',
                        'websiteName' => '',
                        'refererUrl' => '',
                    ],
                    'jobName' => '',
                ],
            ],
            [
                '$importExportResult' => $importExportResultWithFilename,
                '$expected' => [
                    'exportResult' => [
                        'entities' => null,
                        'success' => false,
                        'fileName' => $importExportResultWithFilename->getFilename(),
                        'url' => '',
                        'user' => '',
                        'tryAgainUrl' => '',
                        'websiteName' => '',
                        'refererUrl' => '',
                    ],
                    'jobName' => '',
                ],
            ],
            [
                '$importExportResult' => $importExportResultWithCustomerUser,
                '$expected' => [
                    'exportResult' => [
                        'entities' => null,
                        'success' => false,
                        'fileName' => '',
                        'url' => '',
                        'user' => $customerUser->getFullName(),
                        'tryAgainUrl' => '',
                        'websiteName' => '',
                        'refererUrl' => '',
                    ],
                    'jobName' => '',
                ],
            ],
            [
                '$importExportResult' => $importExportResultWithWebsite,
                '$expected' => [
                    'exportResult' => [
                        'entities' => null,
                        'success' => false,
                        'fileName' => '',
                        'url' => '',
                        'user' => $customerUserWithWebsite->getFullName(),
                        'tryAgainUrl' => 'https://example.org/',
                        'websiteName' => $website->getName(),
                        'refererUrl' => '',
                    ],
                    'jobName' => '',
                ],
            ],
            [
                '$importExportResult' => $importExportResultWithJob,
                '$expected' => [
                    'exportResult' => [
                        'entities' => 'sample2',
                        'success' => true,
                        'fileName' => '',
                        'url' => 'https://example.org/download/42',
                        'user' => $customerUserWithWebsite->getFullName(),
                        'tryAgainUrl' => 'https://example.org/sample/url',
                        'websiteName' => $website->getName(),
                        'refererUrl' => self::REFERER_URL,
                    ],
                    'jobName' => self::ROOT_JOB_NAME,
                ],
            ],
            [
                '$importExportResult' => $importExportResultWithRootJob,
                '$expected' => [
                    'exportResult' => [
                        'entities' => 'sample2',
                        'success' => true,
                        'fileName' => '',
                        'url' => 'https://example.org/download/42',
                        'user' => $customerUserWithWebsite->getFullName(),
                        'tryAgainUrl' => 'https://example.org/sample/url',
                        'websiteName' => $website->getName(),
                        'refererUrl' => self::REFERER_URL,
                    ],
                    'jobName' => self::ROOT_JOB_NAME,
                ],
            ],
        ];
    }
}
