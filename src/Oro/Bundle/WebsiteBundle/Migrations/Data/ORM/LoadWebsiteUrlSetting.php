<?php

namespace Oro\Bundle\WebsiteBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Initializes website URL settings from the application URL configuration.
 */
class LoadWebsiteUrlSetting extends AbstractFixture implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    #[\Override]
    public function setContainer(?ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    #[\Override]
    public function load(ObjectManager $manager)
    {
        $configManager = $this->container->get('oro_config.global');
        if (!$configManager->get('oro_website.url')) {
            $url = $configManager->get('oro_ui.application_url');
            $configManager->set('oro_website.url', $url);
            $configManager->set('oro_website.secure_url', $url);
            $configManager->flush();
        }
    }
}
