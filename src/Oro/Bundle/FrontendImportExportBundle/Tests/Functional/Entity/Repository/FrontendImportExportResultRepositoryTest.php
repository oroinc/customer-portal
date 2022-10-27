<?php

namespace Oro\Bundle\FrontendImportExportBundle\Tests\Functional\Entity\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\FrontendImportExportBundle\Entity\FrontendImportExportResult;
use Oro\Bundle\FrontendImportExportBundle\Tests\Functional\DataFixtures\LoadFrontendImportExportResultData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class FrontendImportExportResultRepositoryTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadFrontendImportExportResultData::class]);
    }

    public function testUpdateExpiredRecords()
    {
        /** @var FrontendImportExportResult $notExpiredResult */
        $notExpiredResult = $this->getReference(LoadFrontendImportExportResultData::NOT_EXPIRED_IMPORT_EXPORT_RESULT);
        $this->assertFalse($notExpiredResult->isExpired());

        $from = new \DateTime('yesterday', new \DateTimeZone('UTC'));
        $to = new \DateTime('tomorrow', new \DateTimeZone('UTC'));

        $repository = $this->getManager()->getRepository(FrontendImportExportResult::class);
        $repository->updateExpiredRecords($from, $to);

        $this->getManager()->refresh($notExpiredResult);

        $this->assertTrue($notExpiredResult->isExpired());
    }

    private function getManager(): EntityManagerInterface
    {
        return self::getContainer()->get('doctrine')->getManagerForClass(FrontendImportExportResult::class);
    }
}
