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
        $mainMenuTrigger = $this->createElement('Main Menu Button');
        $sidebarMainMenuPopup = $this->createElement('Sidebar Main Menu Popup');
        $needClose = false;
        if ($mainMenuTrigger->isIsset() && !$sidebarMainMenuPopup->isIsset()) {
            $mainMenuTrigger->click();
            $needClose = true;
        }

        $this->spin(function () use ($path, $isMenuItemVisibleExpectation) {
            $mainMenu = $this->createElement('FrontendMainMenu');
            $hasLink = $mainMenu->hasLink($path);
            self::assertSame($isMenuItemVisibleExpectation, $hasLink);
            return !is_null($hasLink);
        }, 5);

        if ($needClose) {
            $this->spin(function (): void {
                $close = $this->getPage()->find('css', '[data-role="close"]');
                $close->click();
            }, 5);
        }
    }

    /**
     * Checks, that main menu includes or not exact link and optionally checks the link title
     *
     * Example: Given Main menu should contain "/resource-library" with "Resource Library"
     * Example: Given Main menu should not contain "/resource-library"
     *
     * @Given /^Main menu should(?P<neg>(\s| not ))contain "(?P<link>(?:[^"]|\\")+)"$/
     * @Given /^Main menu should(?P<neg>(\s| not ))contain "(?P<link>(?:[^"]|\\")+)" with "(?P<title>(?:[^"]|\\")+)"$/
     */
    public function mainMenuContainLink(string $link, string $title = '', string $neg = ''): void
    {
        $isMenuItemVisibleExpectation = empty(trim($neg));
        /** @var FrontendMainMenu $mainMenu */
        $mainMenu = $this->createElement('FrontendMainMenu');

        $linkByPath = $mainMenu->find(
            'xpath',
            sprintf('//a[@role="menuitem"][contains(@href, "%s")]', $link)
        );

        if ($isMenuItemVisibleExpectation) {
            self::assertNotNull($linkByPath, sprintf('Menu item with link "%s" not found', $link));
            if ($title) {
                self::assertEquals(
                    strtolower($linkByPath->getText()),
                    strtolower($title),
                    sprintf(
                        'Menu item title mismatch expected: "%s", given: "%s"',
                        $title,
                        $linkByPath->getText()
                    )
                );
            }
        } else {
            self::assertNull($linkByPath, sprintf(
                'Menu item with link "%s" found with title "%s"',
                $link,
                $linkByPath?->getText()
            ));
        }
    }
}
