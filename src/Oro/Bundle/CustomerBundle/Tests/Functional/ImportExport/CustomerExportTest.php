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

    /**
     * Stores content of the exported CSV file
     * @var string
     */
    protected $fileContent;

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
        $this->fileContent = file_get_contents($this->filePath);

        $this->assertNotContains('Addresses', $this->fileContent);
        $this->assertContains('Id', $this->fileContent);
        $this->assertContains('Name', $this->fileContent);
        $this->assertContains('Parent', $this->fileContent);
        $this->assertContains('Group Name', $this->fileContent);
        $this->assertContains(LoadCustomers::CUSTOMER_LEVEL_1_1, $this->fileContent);
        $this->assertContains(LoadCustomers::DEFAULT_ACCOUNT_NAME, $this->fileContent);
        $this->assertSame(16, $jobResult->getContext()->getReadCount());
    }
}
