<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ImportExport;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomers;
use Oro\Bundle\ImportExportBundle\File\FileManager;
use Oro\Bundle\ImportExportBundle\Job\JobExecutor;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolationPerTest
 */
class CustomerExportTest extends WebTestCase
{
    /**
     * @var JobExecutor
     */
    private $jobExecutor;

    /**
     * @var string
     */
    private $filePath;

    protected function setUp()
    {
        $this->initClient();
        $this->loadFixtures([LoadCustomers::class]);
        $this->jobExecutor = self::getContainer()->get('oro_importexport.job_executor');
        $this->filePath = FileManager::generateTmpFilePath(
            FileManager::generateFileName('oro_customer_customer', 'csv')
        );
    }

    protected function tearDown()
    {
        @unlink($this->filePath);
    }

    public function testExport()
    {
        $configuration = [
            'export' => [
                'processorAlias' => 'oro_customer_customer',
                'entityName' => Customer::class,
                'filePath' => $this->filePath,
            ]
        ];

        $jobResult = $this->jobExecutor->executeJob(
            'export',
            'entity_export_to_csv',
            $configuration
        );

        $this->assertTrue($jobResult->isSuccessful());

        $this->assertFileExists($this->filePath);
        $fileContent = file_get_contents($this->filePath);

        $this->assertContains('Id,Name', $fileContent);
        $this->assertNotContains('Addresses', $fileContent);
        $this->assertContains(LoadCustomers::CUSTOMER_LEVEL_1_1, $fileContent);
        $this->assertContains(LoadCustomers::DEFAULT_ACCOUNT_NAME, $fileContent);
        $this->assertSame(16, $jobResult->getContext()->getReadCount());
    }
}
