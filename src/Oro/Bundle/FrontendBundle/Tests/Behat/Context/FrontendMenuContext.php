<?php

namespace Oro\Bundle\FrontendBundle\Tests\Behat\Context;

use Oro\Bundle\FrontendBundle\Tests\Behat\Element\FrontendMainMenu;
use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\OroPageObjectAware;
use Oro\Bundle\TestFrameworkBundle\Tests\Behat\Context\PageObjectDictionary;

/**
 * Provides a set of steps to test storefront menu.
 */
class FrontendMenuContext extends OroFeatureContext implements
    OroPageObjectAware
{
    use PageObjectDictionary;

    /**
     * Assert main menu item existing
     *
     * @Given /^(?:|I )should(?P<negotiation>(\s| not ))see "(?P<path>(?:[^"]|\\")+)" in main menu$/
     */
    public function iShouldSeeOrNotInMainMenu(string $negotiation, string $path): void
    {
        $this->fixStepArgument($path);

        $isMenuItemVisibleExpectation = empty(trim($negotiation));
        /** @var FrontendMainMenu $mainMenu */
        $mainMenu = $this->createElement('FrontendMainMenu');
        $hasLink = $mainMenu->hasLink($path);

        self::assertSame($isMenuItemVisibleExpectation, $hasLink);
    }
}
