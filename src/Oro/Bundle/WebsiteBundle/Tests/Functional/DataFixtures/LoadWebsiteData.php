<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\UserBundle\DataFixtures\UserUtilityTrait;
use Oro\Bundle\WebsiteBundle\Entity\Website;

class LoadWebsiteData extends AbstractFixture implements DependentFixtureInterface
{
    use UserUtilityTrait;

    const WEBSITE1 = 'US';
    const WEBSITE2 = 'Canada';
    const WEBSITE3 = 'CA';

    /**
     * @var array
     */
    protected $webSites = [
        self::WEBSITE1,
        self::WEBSITE2,
        self::WEBSITE3,
    ];

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return ['Oro\Bundle\LocaleBundle\Tests\Functional\DataFixtures\LoadLocalizationData'];
    }

    /**
     * Load websites
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var EntityManager $manager */
        $user = $this->getFirstUser($manager);
        $businessUnit = $user->getOwner();
        $organization = $user->getOrganization();

        // Create websites
        foreach ($this->webSites as $webSiteName) {
            $site = new Website();
            $site->setOwner($businessUnit)
                ->setOrganization($organization)
                ->setName($webSiteName);

            $this->setReference($site->getName(), $site);

            $manager->persist($site);
        }

        $manager->flush();
        $manager->clear();
    }
}
