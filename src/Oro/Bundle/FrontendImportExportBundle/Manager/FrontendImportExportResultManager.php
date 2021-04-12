<?php

namespace Oro\Bundle\FrontendImportExportBundle\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\FrontendImportExportBundle\Entity\FrontendImportExportResult;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Manage, creates and updates FrontendImportExportResult entity.
 */
class FrontendImportExportResultManager
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * @var TokenAccessorInterface
     */
    private $tokenAccessor;

    /**
     * @param ManagerRegistry $manager
     * @param TokenAccessorInterface $tokenAccessor
     */
    public function __construct(ManagerRegistry $manager, TokenAccessorInterface $tokenAccessor)
    {
        $this->registry = $manager;
        $this->tokenAccessor = $tokenAccessor;
    }

    /**
     * @param int $jobId
     * @param string $type
     * @param string $entity
     * @param User|null $owner
     * @param string|null $fileName
     * @param array $options
     *
     * @return FrontendImportExportResult
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function saveResult(
        int $jobId,
        string $type,
        string $entity,
        User $owner = null,
        string $fileName = null,
        array $options = []
    ): FrontendImportExportResult {
        $importExportResult = new FrontendImportExportResult();
        $importExportResult
            ->setJobId($jobId)
            ->setEntity($entity)
            ->setFilename($fileName)
            ->setType($type)
            ->setOptions($options);

        $organization = $this->tokenAccessor->getOrganization();
        if ($organization) {
            $importExportResult->setOrganization($organization);
        }

        if ($owner) {
            $importExportResult->setOwner($owner);
        }

        $user = $this->tokenAccessor->getUser();

        if ($user instanceof CustomerUser) {
            $importExportResult->setCustomer($user->getCustomer());
            $importExportResult->setCustomerUser($user);
        }

        /** @var EntityManager $em */
        $em = $this->registry->getManagerForClass(FrontendImportExportResult::class);
        $em->persist($importExportResult);
        $em->flush();

        return $importExportResult;
    }

    /**
     * @param \DateTime $from
     * @param \DateTime $to
     */
    public function markResultsAsExpired(\DateTime $from, \DateTime $to): void
    {
        $em = $this->registry->getManagerForClass(FrontendImportExportResult::class);
        $importExportResultRepository = $em->getRepository(FrontendImportExportResult::class);
        $importExportResultRepository->updateExpiredRecords($from, $to);

        $em->flush();
    }
}
