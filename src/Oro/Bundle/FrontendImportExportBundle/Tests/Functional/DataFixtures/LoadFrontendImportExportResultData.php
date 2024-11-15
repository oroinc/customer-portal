<?php

namespace Oro\Bundle\FrontendImportExportBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\FrontendImportExportBundle\Entity\FrontendImportExportResult;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;
use Oro\Bundle\UserBundle\Entity\User;

class LoadFrontendImportExportResultData extends AbstractFixture implements DependentFixtureInterface
{
    public const EXPIRED_IMPORT_EXPORT_RESULT = 'expiredFrontendImportExportResult';
    public const NOT_EXPIRED_IMPORT_EXPORT_RESULT = 'notExpiredFrontendImportExportResult';

    private array $importExportResults = [
        self::EXPIRED_IMPORT_EXPORT_RESULT => [
            'jobId' =>  10,
            'expired' => true,
            'entity' => User::class
        ],
        self::NOT_EXPIRED_IMPORT_EXPORT_RESULT => [
            'jobId' =>  120,
            'expired' => false,
            'entity' => User::class
        ]
    ];

    #[\Override]
    public function load(ObjectManager $manager)
    {
        /** @var User $user */
        $user = $this->getReference(LoadUser::USER);
        $customerUser = $this->getReference(LoadCustomerUserData::EMAIL);
        $customer = $customerUser->getCustomer();

        foreach ($this->importExportResults as $reference => $data) {
            $entity = new FrontendImportExportResult();
            $entity->setJobId($data['jobId']);
            $entity->setEntity($data['entity']);
            $entity->setOwner($user);
            $entity->setCustomerUser($customerUser);
            $entity->setCustomer($customer);
            $entity->setOrganization($user->getOrganization());
            $entity->setExpired($data['expired']);
            $entity->setType('export');
            $this->setReference($reference, $entity);
            $manager->persist($entity);
        }

        $manager->flush();
    }

    #[\Override]
    public function getDependencies()
    {
        return [
            LoadUser::class,
            LoadCustomerUserData::class
        ];
    }
}
