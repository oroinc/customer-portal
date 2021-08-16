<?php

namespace Oro\Bundle\FrontendBundle\Tests\Behat\Context;

use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;

class ApiContext extends OroFeatureContext
{
    /**
     * @Given /^(?:|I )enable storefront API$/
     */
    public function setConfigurationProperty()
    {
        $configManager = $this->getAppContainer()->get('oro_config.global');
        $configManager->set('oro_frontend.web_api', true);
        $configManager->flush();
    }
}
