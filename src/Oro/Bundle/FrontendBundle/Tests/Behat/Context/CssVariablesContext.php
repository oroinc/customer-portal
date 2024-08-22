<?php

namespace Oro\Bundle\FrontendBundle\Tests\Behat\Context;

use Behat\Gherkin\Node\TableNode;
use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\OroPageObjectAware;
use Oro\Bundle\TestFrameworkBundle\Tests\Behat\Context\PageObjectDictionary;

/**
 * Provides method to assert css variables on the page
 */
class CssVariablesContext extends OroFeatureContext implements OroPageObjectAware
{
    use PageObjectDictionary;

    /**
     * @Then I should see these theme css variables:
     * Example: And I should see these theme css variables:
     *      | variable       | value |
     *      | primary-main   | #000  |
     *      | base-font-size | 1em   |
     */
    public function assertThemeCssVariables(TableNode $table)
    {
        $variables = $table->getHash();
        $html = $this->getSession()->getPage()->getContent();

        // Use a regular expression to find the content inside :root { ... }
        preg_match('/<style>.*?:root\s*{([^}]*)}.*?<\/style>/is', $html, $matches);

        if (!isset($matches[1])) {
            throw new \Exception(":root CSS variables block not found in the page.");
        }

        $rootContent = trim($matches[1]);

        foreach ($variables as $variable) {
            $variableName = $variable['variable'];
            $variableValue = $variable['value'];
            $cssText = sprintf('--%s: %s', $variableName, $variableValue);

            self::assertStringContainsString(
                $cssText,
                $rootContent,
                "The CSS variable '$cssText' was not found in the HTML."
            );
        }
    }

    /**
     * @Then /^I set color '(?P<value>[^']*)' for (?P<element>.+)$/
     */
    public function setColor($value, $element)
    {
        $formElement = $this->getPage()->getElement($element)->getParent();

        $this->elementFactory->wrapElement('Simple Color Picker Field', $formElement)->setValue($value);
    }
}
