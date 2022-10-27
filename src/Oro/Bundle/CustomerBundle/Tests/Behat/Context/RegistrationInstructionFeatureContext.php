<?php

namespace Oro\Bundle\CustomerBundle\Tests\Behat\Context;

use Behat\Gherkin\Node\TableNode;
use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\Form;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\OroPageObjectAware;
use Oro\Bundle\TestFrameworkBundle\Tests\Behat\Context\PageObjectDictionary;

class RegistrationInstructionFeatureContext extends OroFeatureContext implements OroPageObjectAware
{
    use PageObjectDictionary;

    /**
     * Example: I fill "Routing General form" with actual website
     *
     * @Given /^I fill "(?P<formName>(?:[^"]|\\")*)" with actual website$/
     *
     * @param string $formName
     */
    public function iFillWithActualWebsite($formName)
    {
        $url = $this->getCurrentApplicationUrl();

        $this->fillWebsiteTableWithUrl($formName, $url);
    }

    /**
     * Example: I fill "Routing General form" with fictional website
     *
     * @Given /^I fill "(?P<formName>(?:[^"]|\\")*)" with fictional website$/
     *
     * @param string $formName
     */
    public function iFillWithFictionalWebsite($formName)
    {
        $url = $this->getCurrentApplicationUrl('test.');

        $this->fillWebsiteTableWithUrl($formName, $url);
    }

    /**
     * @param string $subDomain
     *
     * @return string
     */
    private function getCurrentApplicationUrl($subDomain = '')
    {
        $currentUrl = $this->getSession()->getCurrentUrl();
        $scheme = parse_url($currentUrl, PHP_URL_SCHEME);
        $host = parse_url($currentUrl, PHP_URL_HOST);
        $port = parse_url($currentUrl, PHP_URL_PORT);
        if ($port) {
            $port = ':' . $port;
        }
        return sprintf('%s://%s%s%s/', $scheme, $subDomain, $host, $port);
    }

    /**
     * @param string $formName
     * @param string $url
     */
    private function fillWebsiteTableWithUrl($formName, $url)
    {
        $table = new TableNode([
            ['URL Use System', "false"],
            ['URL', $url],
            ['Secure URL Use System', "false"],
            ['Secure URL', $url],
        ]);
        /** @var Form $form */
        $form = $this->createElement($formName);
        $form->fill($table);
    }
}
