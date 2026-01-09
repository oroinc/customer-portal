<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\DBAL\Event\ConnectionEventArgs;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\LocaleBundle\Tests\Functional\DataFixtures\LoadLocalizationData;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Provider\CacheableWebsiteProvider;
use Oro\Component\DependencyInjection\ContainerAwareInterface;
use Oro\Component\DependencyInjection\ContainerAwareTrait;
use Oro\Component\Testing\Doctrine\Events;

class LoadWebsiteData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public const WEBSITE1 = 'US';
    public const WEBSITE2 = 'Canada';
    public const WEBSITE3 = 'CA';

    /**
     * @var array
     */
    protected $webSites = [
        self::WEBSITE1,
        self::WEBSITE2,
        self::WEBSITE3,
    ];

    #[\Override]
    public function getDependencies()
    {
        return [
            LoadUser::class,
            LoadLocalizationData::class
        ];
    }

    /**
     * Load websites
     */
    #[\Override]
    public function load(ObjectManager $manager)
    {
        /** @var User $user */
        $user = $this->getReference(LoadUser::USER);
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

        $manager->getConnection()
            ->getEventManager()
            ->addEventListener(Events::ON_AFTER_TEST_TRANSACTION_ROLLBACK, $this);
    }

    /**
     * Will be executed when (if) this fixture will be rolled back
     */
    public function onAfterTestTransactionRollback(ConnectionEventArgs $args)
    {
        /** @var CacheableWebsiteProvider $provider */
        $provider = $this->container->get('oro_website.cacheable_website_provider');
        $provider->clearCache();
    }
}
