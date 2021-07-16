<?php

namespace Oro\Bundle\FrontendImportExportBundle\Manager;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\FrontendImportExportBundle\Entity\FrontendImportExportResult;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;

/**
 * Manage, creates and updates FrontendImportExportResult entity.
 */
class FrontendImportExportResultManager
{
    private ManagerRegistry $managerRegistry;

    private TokenAccessorInterface $tokenAccessor;

    public function __construct(ManagerRegistry $managerRegistry, TokenAccessorInterface $tokenAccessor)
    {
        $this->managerRegistry = $managerRegistry;
        $this->tokenAccessor = $tokenAccessor;
    }

    public function saveResult(
        int $jobId,
        string $type,
        string $entity,
        CustomerUser $customerUser,
        string $fileName = null,
        array $options = []
    ): FrontendImportExportResult {
        if ($this->tokenAccessor->getUserId() === $customerUser->getId()) {
            $organization = $this->tokenAccessor->getOrganization() ?? $customerUser->getOrganization();
        } else {
            $organization = $customerUser->getOrganization();
        }

        $importExportResult = (new FrontendImportExportResult())
            ->setJobId($jobId)
            ->setEntity($entity)
            ->setFilename($fileName)
            ->setType($type)
            ->setOptions($options)
            ->setCustomerUser($customerUser)
            ->setOwner($customerUser->getOwner())
            ->setOrganization($organization);

        /** @var ObjectManager $entityManager */
        $entityManager = $this->managerRegistry->getManagerForClass(FrontendImportExportResult::class);
        $entityManager->persist($importExportResult);
        $entityManager->flush();

        return $importExportResult;
    }

    public function markResultsAsExpired(\DateTime $from, \DateTime $to): void
    {
        /** @var ObjectManager $entityManager */
        $entityManager = $this->managerRegistry->getManagerForClass(FrontendImportExportResult::class);
        $importExportResultRepository = $entityManager->getRepository(FrontendImportExportResult::class);
        $importExportResultRepository->updateExpiredRecords($from, $to);

        $entityManager->flush();
    }
}
