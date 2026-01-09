<?php

namespace Oro\Bundle\FrontendBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Component\DependencyInjection\ContainerAwareInterface;
use Oro\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Fixture that enables frontend API feature.
 */
class EnableFrontendApiFeature extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    #[\Override]
    public function load(ObjectManager $manager)
    {
        $configManager = $this->container->get('oro_config.global');
        $configManager->set('oro_frontend.web_api', true);
        $configManager->flush();
    }
}
