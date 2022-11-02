<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\TestFrameworkBundle\Test\DataFixtures\InitialFixtureInterface;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * Loads the default website belongs to the first organization from the database.
 */
class LoadWebsite extends AbstractFixture implements InitialFixtureInterface, DependentFixtureInterface
{
    public const WEBSITE = 'website';

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [LoadOrganization::class];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $website = $manager->getRepository(Website::class)
            ->createQueryBuilder('t')
            ->where('t.organization = :organization AND t.default = :default')
            ->setParameter('organization', $this->getReference(LoadOrganization::ORGANIZATION))
            ->setParameter('default', true)
            ->orderBy('t.id')
            ->getQuery()
            ->setMaxResults(1)
            ->getSingleResult();
        $this->addReference(self::WEBSITE, $website);
    }
}
