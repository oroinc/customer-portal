<?php

namespace Oro\Bundle\FrontendImportExportBundle\Tests\Unit\Async;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRepository;
use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Bundle\FrontendImportExportBundle\Async\Export\FrontendExportResultSummarizer;
use Oro\Bundle\ImportExportBundle\Exception\LogicException;
use Oro\Bundle\MessageQueueBundle\Entity\Job;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\TestCase;

class FrontendExportResultSummarizerTest extends TestCase
{
    use EntityTrait;

    /** @var WebsiteUrlResolver|\PHPUnit\Framework\MockObject\MockObject  */
    private WebsiteUrlResolver $websiteUrlResolver;

    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject  */
    private ManagerRegistry $managerRegistry;

    /** @var CustomerUserRepository|\PHPUnit\Framework\MockObject\MockObject  */
    private CustomerUserRepository $customerUserRepository;

    /** @var EntityNameResolver|\PHPUnit\Framework\MockObject\MockObject */
    private EntityNameResolver $entityNameResolver;
    private FrontendExportResultSummarizer $resultSummarizer;
    private CustomerUser $user;

    protected function setUp(): void
    {
        $this->websiteUrlResolver = $this->createMock(WebsiteUrlResolver::class);
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->entityNameResolver = $this->createMock(EntityNameResolver::class);

        $this->customerUserRepository = $this->createMock(CustomerUserRepository::class);
        $this->managerRegistry->expects($this->once())
            ->method('getRepository')
            ->willReturn($this->customerUserRepository);

        $website = $this->getEntity(Website::class, [
            'name' => 'Test website'
        ]);
        $this->user = $this->getEntity(CustomerUser::class, [
            'firstName' => 'Test',
            'lastName' => 'Customer',
            'website' => $website
        ]);

        $this->resultSummarizer = new FrontendExportResultSummarizer(
            $this->websiteUrlResolver,
            $this->managerRegistry,
            $this->entityNameResolver
        );
    }

    public function testprocessSummaryExportResultForNotification(): void
    {
        $data = [
            'success' => true,
            'errors' => [],
            'readsCount' => 5,
            'errorsCount' => 0,
        ];

        $this->customerUserRepository->expects($this->once())
            ->method('find')
            ->willReturn($this->user);

        $job = new Job();
        $job->setId(12345);

        $childJob1 = new Job();
        $childJob1->setData($data);
        $job->addChildJob($childJob1);

        $childJob2 = new Job();
        $childJob2->setData($data);
        $job->addChildJob($childJob2);

        $this->websiteUrlResolver->expects($this->once())
            ->method('getWebsitePath')
            ->willReturn(
                sprintf('https://localhost/download/%d', $job->getId())
            );

        $this->entityNameResolver->expects($this->once())
            ->method('getName')
            ->willReturn('Test Customer');

        $result = $this->resultSummarizer->processSummaryExportResultForNotification(
            $job,
            'import.csv',
            1,
            'https://localhost/product'
        );

        $expectedData = [
            'entities' => null,
            'success' => true,
            'fileName' => 'import.csv',
            'url' => 'https://localhost/download/12345',
            'user' => 'Test Customer',
            'tryAgainUrl' => 'https://localhost/product',
            'websiteName' => 'Test website'
        ];

        $this->assertArrayHasKey('exportResult', $result);
        $this->assertEquals($expectedData, $result['exportResult']);
    }

    public function testProcessSummaryExportResultWithErrors(): void
    {
        $data = [
            'success' => false,
            'errors' => [
                "Backend header doesn't contain fields: msrp, map"
            ],
            'readsCount' => 0,
            'errorsCount' => 1,
            'errorLogFile' => 'export60467ebf016a8686417841.json'
        ];

        $this->customerUserRepository->expects($this->once())
            ->method('find')
            ->willReturn($this->user);

        $job = new Job();
        $job->setId(12345);

        $childJob1 = new Job();
        $childJob1->setData($data);
        $job->addChildJob($childJob1);

        $childJob2 = new Job();
        $childJob2->setData($data);
        $job->addChildJob($childJob2);

        $this->websiteUrlResolver->expects($this->once())
            ->method('getWebsitePath')
            ->willReturn(
                sprintf('https://localhost/download/%d', $job->getId())
            );

        $this->entityNameResolver->expects($this->once())
            ->method('getName')
            ->willReturn('Test Customer');

        $result = $this->resultSummarizer->processSummaryExportResultForNotification(
            $job,
            'import.csv',
            1,
            'https://localhost/product'
        );

        $expectedData = [
            'entities' => null,
            'success' => false,
            'fileName' => 'import.csv',
            'url' => 'https://localhost/download/12345',
            'user' => 'Test Customer',
            'tryAgainUrl' => 'https://localhost/product',
            'websiteName' => 'Test website'
        ];

        $this->assertArrayHasKey('exportResult', $result);
        $this->assertEquals($expectedData, $result['exportResult']);
    }

    public function testProcessSummaryExportResultIfUserNotFound(): void
    {
        $data = [
            'success' => true,
            'errors' => [],
            'readsCount' => 5,
            'errorsCount' => 0,
        ];

        $job = new Job();
        $job->setId(12345);

        $childJob1 = new Job();
        $childJob1->setData($data);
        $job->addChildJob($childJob1);

        $childJob2 = new Job();
        $childJob2->setData($data);
        $job->addChildJob($childJob2);

        $this->customerUserRepository->expects($this->once())
            ->method('find')
            ->willReturn(null);

        $this->websiteUrlResolver->expects($this->never())
        ->method('getWebsitePath');

        $this->websiteUrlResolver->expects($this->never())
            ->method('getWebsiteSecurePath');

        $this->entityNameResolver->expects($this->never())
            ->method('getName');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            sprintf('Current user with id 2 is not found')
        );

        $this->resultSummarizer->processSummaryExportResultForNotification($job, 'import.csv', 2);
    }

    public function testProcessSummaryExportResultForNotificationWithoutRefererUrl()
    {
        $data = [
            'success' => true,
            'errors' => [],
            'readsCount' => 5,
            'errorsCount' => 0,
        ];

        $this->customerUserRepository->expects($this->once())
            ->method('find')
            ->willReturn($this->user);

        $job = new Job();
        $job->setId(12345);

        $childJob1 = new Job();
        $childJob1->setData($data);
        $job->addChildJob($childJob1);

        $childJob2 = new Job();
        $childJob2->setData($data);
        $job->addChildJob($childJob2);

        $this->websiteUrlResolver->expects($this->once())
            ->method('getWebsitePath')
            ->willReturn(
                sprintf('https://localhost/download/%d', $job->getId())
            );

        $this->websiteUrlResolver->expects($this->once())
            ->method('getWebsiteSecurePath')
            ->willReturn(
                'https://localhost/homepage'
            );

        $this->entityNameResolver->expects($this->once())
            ->method('getName')
            ->willReturn('Test Customer');

        $result = $this->resultSummarizer->processSummaryExportResultForNotification(
            $job,
            'import.csv',
            1,
            null
        );

        $expectedData = [
            'entities' => null,
            'success' => true,
            'fileName' => 'import.csv',
            'url' => 'https://localhost/download/12345',
            'user' => 'Test Customer',
            'tryAgainUrl' => 'https://localhost/homepage',
            'websiteName' => 'Test website'
        ];

        $this->assertArrayHasKey('exportResult', $result);
        $this->assertEquals($expectedData, $result['exportResult']);
    }
}
