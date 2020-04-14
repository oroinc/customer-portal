<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ImportExport\Import;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\CustomerBundle\Migrations\Data\Demo\ORM\LoadCustomerUserDemoData;
use Oro\Bundle\ImportExportBundle\Job\JobExecutor;
use Oro\Bundle\ImportExportBundle\Job\JobResult;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\IntegrationBundle\Test\FakeRestClientFactory;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolationPerTest
 */
class ImportCustomerUserTest extends WebTestCase
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var FakeRestClientFactory
     */
    protected static $fakeRestClientFactory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->initClient();

        $this->loadFixtures([
            LoadCustomerUserDemoData::class
        ]);
    }

    /**
     * @dataProvider getFixtureProvider
     *
     * @param string $fixtureName
     */
    public function testImport($fixtureName)
    {
        $jobResult = $this->executeJob($this->getImportFixture($fixtureName));

        $this->assertTrue($jobResult->isSuccessful());
    }

    /**
     * @return array
     */
    public function getFixtureProvider()
    {
        return [
            'customer_users_with_existing_id_and_not_valid_customer' => [
                'fixtureName' => 'customer_users_with_existing_id_and_not_valid_customer',
            ]
        ];
    }

    /**
     * @param string $fileName
     *
     * @return JobResult
     */
    private function executeJob($fileName)
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

    /**
     * @param string $fixtureName
     *
     * @return string
     */
    private function getImportFixture($fixtureName)
    {
        return sprintf('%s/fixtures/%s.csv', __DIR__, $fixtureName);
    }
}
