<?php

namespace Oro\Bundle\CustomerBundle\Tests\Behat;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRoleRepository;
use Oro\Bundle\TestFrameworkBundle\Behat\Isolation\ReferenceRepositoryInitializerInterface;
use Oro\Bundle\TestFrameworkBundle\Test\DataFixtures\Collection;
use Oro\Bundle\TranslationBundle\Entity\TranslationKey;

class ReferenceRepositoryInitializer implements ReferenceRepositoryInitializerInterface
{
    /**
     * {@inheritdoc}
     */
    public function init(ManagerRegistry $doctrine, Collection $referenceRepository): void
    {
        /** @var CustomerUserRoleRepository $repository */
        $repository = $doctrine->getManager()->getRepository(CustomerUserRole::class);
        $buyer = $repository->findOneBy(['role' => 'ROLE_FRONTEND_BUYER']);
        $referenceRepository->set('buyer', $buyer);

        $nonAuthenticatedVisitors = $repository->findOneBy(['role' => 'ROLE_FRONTEND_ANONYMOUS']);
        $referenceRepository->set('non_authenticated_visitors', $nonAuthenticatedVisitors);

        $administrator = $repository->findOneBy(['role' => 'ROLE_FRONTEND_ADMINISTRATOR']);
        $referenceRepository->set('front_admin', $administrator);

        $referenceRepository->set(
            'oro_customer_user_all_grid_view_label',
            $doctrine->getManager()->getRepository(TranslationKey::class)->findOneBy([
                'key' => 'oro.customer.customeruser.entity_frontend_grid_all_view_label',
                'domain' => 'messages'
            ])
        );
    }
}
