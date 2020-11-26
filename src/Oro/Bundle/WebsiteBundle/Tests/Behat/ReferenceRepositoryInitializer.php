<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Behat;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Oro\Bundle\ScopeBundle\Entity\Scope;
use Oro\Bundle\TestFrameworkBundle\Behat\Isolation\ReferenceRepositoryInitializerInterface;
use Oro\Bundle\TestFrameworkBundle\Test\DataFixtures\Collection;
use Oro\Bundle\WebsiteBundle\Entity\Repository\WebsiteRepository;

class ReferenceRepositoryInitializer implements ReferenceRepositoryInitializerInterface
{
    /**
     * {@inheritdoc}
     */
    public function init(Registry $doctrine, Collection $referenceRepository)
    {
        /** @var WebsiteRepository $repository */
        $repository = $doctrine->getManager()->getRepository('OroWebsiteBundle:Website');

        $referenceRepository->set('website1', $repository->findOneBy(['id' => '1']));

        $repository = $doctrine->getRepository(Scope::class);

        $referenceRepository->set('first_website_scope', $repository->findOneBy(['id' => 2]));
    }
}
