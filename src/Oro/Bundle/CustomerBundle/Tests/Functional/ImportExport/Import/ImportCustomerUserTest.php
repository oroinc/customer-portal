<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ImportExport\Import;

use Oro\Bundle\CustomerBundle\Migrations\Data\Demo\ORM\LoadCustomerUserDemoData;
use Oro\Bundle\ImportExportBundle\Job\JobExecutor;
use Oro\Bundle\ImportExportBundle\Job\JobResult;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolationPerTest
 */
class ImportCustomerUserTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadCustomerUserDemoData::class]);
    }

    /**
     * @dataProvider getFixtureProvider
     */
    public function testImport(string $fixtureName)
    {
        $jobResult = $this->executeJob($this->getImportFixture($fixtureName));

        $this->assertTrue($jobResult->isSuccessful());
    }

    public function getFixtureProvider(): array
    {
        return [
            'customer_users_with_existing_id_and_not_valid_customer' => [
                'fixtureName' => 'customer_users_with_existing_id_and_not_valid_customer',
            ]
        ];
    }

    private function executeJob(string $fileName): JobResult
    {
        /** @var JobExecutor $executor */
        $executor = $this->getContainer()->get('oro_importexport.job_executor');
        $entityName = $this->getContainer()->get('oro_importexport.processor.registry')->getProcessorEntityName(
            ProcessorRegistry::TYPE_IMPORT,
            'oro_customer_customer_user'
        );

        return $executor->executeJob(
            ProcessorRegistry::TYPE_IMPORT,
            JobExecutor::JOB_IMPORT_FROM_CSV,
            [
                'import' => [
                    'processorAlias' => 'oro_customer_customer_user',
                    'entityName' => $entityName,
                    'filePath' => $fileName
                ],
            ]
        );
    }

    private function getImportFixture(string $fixtureName): string
    {
        return sprintf('%s/fixtures/%s.csv', __DIR__, $fixtureName);
    }
}
