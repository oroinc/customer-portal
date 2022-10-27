<?php

namespace Oro\Bundle\FrontendImportExportBundle\Tests\Unit\Manager;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Tests\Unit\Stub\CustomerUserStub;
use Oro\Bundle\FrontendImportExportBundle\Entity\FrontendImportExportResult;
use Oro\Bundle\FrontendImportExportBundle\Entity\Repository\FrontendImportExportResultRepository;
use Oro\Bundle\FrontendImportExportBundle\Manager\FrontendImportExportResultManager;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\TestCase;

class FrontendImportExportResultManagerTest extends TestCase
{
    use EntityTrait;

    private const TOKEN_USER_ID = 42;

    /** @var ObjectManager|\PHPUnit\Framework\MockObject\MockObject  */
    private $entityManager;

    /** @var FrontendImportExportResultManager */
    private $manager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(ObjectManager::class);

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects(self::any())
            ->method('getManagerForClass')
            ->willReturn($this->entityManager);

        $tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $tokenAccessor->expects(self::any())
            ->method('getOrganization')
            ->willReturn(self::getTokenOrganization());
        $tokenAccessor->expects(self::any())
            ->method('getUserid')
            ->willReturn(self::TOKEN_USER_ID);

        $this->manager = new FrontendImportExportResultManager($doctrine, $tokenAccessor);
    }

    /**
     * @dataProvider saveResultDataProvider
     */
    public function testSaveResult(
        int $jobId,
        string $type,
        string $entity,
        CustomerUser $customerUser,
        string $fileName,
        array $options,
        FrontendImportExportResult $expected
    ): void {
        $this->entityManager->expects(self::once())
            ->method('persist');

        $this->entityManager->expects(self::once())
            ->method('flush');

        $importExportResult = $this->manager->saveResult($jobId, $type, $entity, $customerUser, $fileName, $options);

        self::assertEquals($expected, $importExportResult);
    }

    public function saveResultDataProvider(): array
    {
        $customer = new Customer();
        $customerUser = new CustomerUserStub(142);
        $customerUser->setOrganization(new Organization());
        $customerUser->setCustomer($customer);

        $customerUserFromToken = new CustomerUserStub(self::TOKEN_USER_ID);
        $customerUserFromToken->setCustomer($customer);

        $importExportResult = (new FrontendImportExportResult())
            ->setJobId(123)
            ->setType('export')
            ->setEntity(\stdClass::class)
            ->setFilename('sample_file.csv')
            ->setOptions(['sample_option' => 'sample_value']);

        return [
            'organization from token' => [
                '$jobId' => 123,
                '$type' => 'export',
                '$entity' => \stdClass::class,
                '$customerUser' => $customerUserFromToken,
                '$fileName' => 'sample_file.csv',
                '$options' => ['sample_option' => 'sample_value'],
                '$expected' => (clone $importExportResult)
                    ->setCustomerUser($customerUserFromToken)
                    ->setOrganization(self::getTokenOrganization()),
            ],
            'organization from customerUser' => [
                '$jobId' => 123,
                '$type' => 'export',
                '$entity' => \stdClass::class,
                '$customerUser' => $customerUser,
                '$fileName' => 'sample_file.csv',
                '$options' => ['sample_option' => 'sample_value'],
                '$expected' => (clone $importExportResult)
                    ->setCustomerUser($customerUser)
                    ->setOrganization($customerUser->getOrganization()),
            ],
        ];
    }

    public function testMarkResultsAsExpired(): void
    {
        $date = new \DateTime('now', new \DateTimeZone('UTC'));

        $importExportRepository = $this->createMock(FrontendImportExportResultRepository::class);
        $importExportRepository->expects(self::once())
            ->method('updateExpiredRecords')
            ->with($date, $date);

        $this->entityManager->expects(self::once())
            ->method('getRepository')
            ->with(FrontendImportExportResult::class)
            ->willReturn($importExportRepository);

        $this->entityManager->expects(self::once())
            ->method('flush');

        $this->manager->markResultsAsExpired($date, $date);
    }

    private static function getTokenOrganization(): Organization
    {
        static $organization;
        if (!$organization) {
            $organization = new Organization();
        }

        return $organization;
    }
}
