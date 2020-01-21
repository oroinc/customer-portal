<?php

namespace Oro\Bundle\FrontendBundle\Tests\Behat\Context;

use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;

class ApiContext extends OroFeatureContext implements KernelAwareContext
{
    use KernelDictionary;

    /**
     * @Given /^(?:|I )enable storefront API$/
     */
    public function setConfigurationProperty()
    {
        $configManager = $this->getContainer()->get('oro_config.global');
        $configManager->set('oro_frontend.web_api', true);
        $configManager->flush();
    }
}
