<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ImportExport\Import;

use Oro\Bundle\CustomerBundle\Tests\Functional\ImportExport\Import\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\ImportExportBundle\Job\JobExecutor;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolationPerTest
 */
class ImportCustomerUserTest extends WebTestCase
{
    #[\Override]
    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadCustomerUserData::class]);
    }

    /**
     * @dataProvider getFixtureProvider
     */
    public function testImport(string $fixtureName): void
    {
        /** @var JobExecutor $executor */
        $executor = self::getContainer()->get('oro_importexport.job_executor');
        $entityName = self::getContainer()->get('oro_importexport.processor.registry')->getProcessorEntityName(
            ProcessorRegistry::TYPE_IMPORT,
            'oro_customer_customer_user'
        );
        $jobResult = $executor->executeJob(
            ProcessorRegistry::TYPE_IMPORT,
            JobExecutor::JOB_IMPORT_FROM_CSV,
            [
                'import' => [
                    'processorAlias' => 'oro_customer_customer_user',
                    'entityName' => $entityName,
                    'filePath' => sprintf('%s/fixtures/%s.csv', __DIR__, $fixtureName)
                ]
            ]
        );

        self::assertTrue($jobResult->isSuccessful());
    }

    public static function getFixtureProvider(): array
    {
        return [
            'customer_users_with_existing_id_and_not_valid_customer' => [
                'fixtureName' => 'customer_users_with_existing_id_and_not_valid_customer',
            ]
        ];
    }
}
