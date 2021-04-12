<?php

namespace Oro\Bundle\FrontendImportExportBundle\Tests\Unit\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\FrontendImportExportBundle\Entity\FrontendImportExportResult;
use Oro\Bundle\FrontendImportExportBundle\Entity\Repository\FrontendImportExportResultRepository;
use Oro\Bundle\FrontendImportExportBundle\Manager\FrontendImportExportResultManager;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\TestCase;

class FrontendImportExportResultManagerTest extends TestCase
{
    use EntityTrait;

    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject  */
    private ManagerRegistry $registry;

    /** @var TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject  */
    private TokenAccessorInterface $tokenAccessor;

    /** @var EntityManager|\PHPUnit\Framework\MockObject\MockObject  */
    private EntityManager $entityManager;

    private FrontendImportExportResultManager $manager;

    protected function setUp(): void
    {
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $this->registry = $this->createMock(ManagerRegistry::class);

        $this->entityManager = $this->createMock(EntityManager::class);
        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->willReturn($this->entityManager);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->manager = new FrontendImportExportResultManager(
            $this->registry,
            $this->tokenAccessor
        );
    }

    /**
     * @dataProvider saveResultDataProvider
     *
     * @param array $actual
     * @param array $expected
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testSaveResult(array $actual, array $expected): void
    {
        $this->tokenAccessor->expects($this->once())
            ->method('getOrganization')
            ->willReturn($expected['organization']);

        $this->tokenAccessor->expects($this->once())
            ->method('getUser')
            ->willReturn($expected['customerUser']);

        $this->entityManager->expects($this->once())
            ->method('persist');

        $result = $this->manager->saveResult(
            $actual['jobId'],
            $actual['type'],
            $actual['entity'],
            $actual['owner'],
            $actual['filename'],
            $actual['options']
        );

        $expectedResult = $this->getEntity(FrontendImportExportResult::class, $expected);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function saveResultDataProvider(): array
    {
        $user = new User();
        $organization = new Organization();
        $user->setOrganization($organization);

        $customer = new Customer();
        $customerUser = new CustomerUser();
        $customerUser->setCustomer($customer);

        return [
            'without owner' => [
                'actual' => [
                    'jobId' => 123,
                    'owner' => null,
                    'type' => 'export',
                    'filename' => 'file.csv',
                    'entity' => 'Acme',
                    'options' => [],
                ],
                'expected' => [
                    'jobId' => 123,
                    'type' => 'export',
                    'filename' => 'file.csv',
                    'entity' => 'Acme',
                    'options' => [],
                    'organization' => $organization,
                    'customerUser' => $customerUser,
                    'customer' => $customer
                ],
            ],
            'with owner' => [
                'actual' => [
                    'jobId' => 123,
                    'owner' => $user,
                    'type' => 'import_or_export',
                    'filename' => 'file.csv',
                    'entity' => 'Acme',
                    'options' => []
                ],
                'expected' => [
                    'jobId' => 123,
                    'owner' => $user,
                    'type' => 'import_or_export',
                    'filename' => 'file.csv',
                    'entity' => 'Acme',
                    'options' => [],
                    'organization' => $organization,
                    'customerUser' => $customerUser,
                    'customer' => $customer
                ],
            ],
            'with options' => [
                'actual' => [
                    'jobId' => 123,
                    'owner' => null,
                    'type' => 'import_or_export',
                    'filename' => 'file.csv',
                    'entity' => 'Acme',
                    'options' => ['test1' => 'test2']
                ],
                'expected' => [
                    'jobId' => 123,
                    'type' => 'import_or_export',
                    'filename' => 'file.csv',
                    'entity' => 'Acme',
                    'options' => ['test1' => 'test2'],
                    'organization' => $organization,
                    'customerUser' => $customerUser,
                    'customer' => $customer
                ],
            ],
            'without customer user' => [
                'actual' => [
                    'jobId' => 123,
                    'owner' => null,
                    'type' => 'import_or_export',
                    'filename' => 'file.csv',
                    'entity' => 'Acme',
                    'options' => ['test1' => 'test2'],
                ],
                'expected' => [
                    'jobId' => 123,
                    'type' => 'import_or_export',
                    'filename' => 'file.csv',
                    'entity' => 'Acme',
                    'options' => ['test1' => 'test2'],
                    'organization' => $organization,
                    'customerUser' => null,
                    'customer' => null
                ],
            ]
        ];
    }

    public function testMarkResultsAsExpired(): void
    {
        $date = new \DateTime('now', new \DateTimeZone('UTC'));

        $importExportRepository = $this->createMock(FrontendImportExportResultRepository::class);
        $importExportRepository
            ->expects($this->once())
            ->method('updateExpiredRecords')
            ->with($date, $date);

        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(FrontendImportExportResult::class)
            ->willReturn($importExportRepository);

        $this->manager->markResultsAsExpired($date, $date);
    }
}
