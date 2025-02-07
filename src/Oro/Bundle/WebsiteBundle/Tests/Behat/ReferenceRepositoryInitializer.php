<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Behat;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ScopeBundle\Entity\Scope;
use Oro\Bundle\TestFrameworkBundle\Behat\Isolation\ReferenceRepositoryInitializerInterface;
use Oro\Bundle\TestFrameworkBundle\Test\DataFixtures\Collection;
use Oro\Bundle\WebsiteBundle\Entity\Website;

class ReferenceRepositoryInitializer implements ReferenceRepositoryInitializerInterface
{
    #[\Override]
    public function init(ManagerRegistry $doctrine, Collection $referenceRepository): void
    {
        $repository = $doctrine->getManager()->getRepository(Website::class);
        $referenceRepository->set('website1', $repository->findOneBy(['id' => '1']));

        $repository = $doctrine->getRepository(Scope::class);
        $referenceRepository->set('first_website_scope', $repository->findOneBy(['id' => 2]));
    }
}
