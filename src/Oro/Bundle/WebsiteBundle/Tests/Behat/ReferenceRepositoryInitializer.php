<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Behat;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Oro\Bundle\TestFrameworkBundle\Behat\Isolation\ReferenceRepositoryInitializerInterface;
use Oro\Bundle\TestFrameworkBundle\Test\DataFixtures\Collection;
use Oro\Bundle\WebsiteBundle\Entity\Repository\WebsiteRepository;
use Oro\Bundle\WebsiteBundle\Entity\Website;

class ReferenceRepositoryInitializer implements ReferenceRepositoryInitializerInterface
{
    /**
     * {@inheritdoc}
     */
    public function init(Registry $doctrine, Collection $referenceRepository)
    {
        /** @var WebsiteRepository $repository */
        $repository = $doctrine->getManager()->getRepository('OroWebsiteBundle:Website');
        /** @var Website $website1*/
        $website1 = $repository->findOneBy(['id' => '1']);
        $referenceRepository->set('website1', $website1);
    }
}
