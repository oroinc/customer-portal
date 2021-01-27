<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ConfigBundle\Entity\Config;
use Oro\Bundle\ConfigBundle\Entity\ConfigValue;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadWebsiteConfigData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var array
     */
    protected $webSitesConfig = [
        [
            'website' => LoadWebsiteData::WEBSITE1,
            'config_values' => [
                'url' => 'http://www.us.com',
                'secure_url' => 'https://www.us.com'
            ]
        ],
        [
            'website' => LoadWebsiteData::WEBSITE2,
            'config_values' => [
                'url' => 'http://www.canada.com',
                'secure_url' => 'https://www.canada.com'
            ]
        ],
        [
            'website' => LoadWebsiteData::WEBSITE3,
            'config_values' => [
                'url' => 'http://www.canada-new.com',
                'secure_url' => 'https://www.canada-new.com'
            ]
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [LoadWebsiteData::class];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        // Config uses non-default manager
        $manager = $this->container->get('doctrine')->getManagerForClass(Config::class);

        foreach ($this->webSitesConfig as $item) {
            $config = new Config();
            /** @var Website $website */
            $website = $this->getReference($item['website']);
            $config->setScopedEntity('website')
                ->setRecordId($website->getId());

            $values = new ArrayCollection();
            foreach ($item['config_values'] as $key => $value) {
                $configValue = new ConfigValue();
                $configValue->setConfig($config)
                    ->setValue($value)
                    ->setSection('oro_website')
                    ->setName($key);

                $values->add($configValue);
            }

            $config->setValues($values);

            $manager->persist($config);
        }

        $manager->flush();
        $manager->clear();
    }
}
