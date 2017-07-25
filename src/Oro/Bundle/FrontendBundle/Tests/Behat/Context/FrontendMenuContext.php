<?php

namespace Oro\Bundle\FrontendBundle\Tests\Behat\Context;

use Oro\Bundle\FrontendBundle\Tests\Behat\Element\FrontendMainMenu;
use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\OroPageObjectAware;
use Oro\Bundle\TestFrameworkBundle\Behat\Isolation\MessageQueueIsolatorInterface;
use Oro\Bundle\TestFrameworkBundle\Tests\Behat\Context\PageObjectDictionary;

class FrontendMenuContext extends OroFeatureContext implements
    OroPageObjectAware
{
    use PageObjectDictionary;

    /**
     * @var MessageQueueIsolatorInterface
     */
    protected $messageQueueIsolator;

    /**
     * {@inheritdoc}
     */
    public function setMessageQueueIsolator(MessageQueueIsolatorInterface $messageQueueIsolator)
    {
        $this->messageQueueIsolator = $messageQueueIsolator;
    }

    /**
     * Assert main menu item existing
     *
     * @Given /^(?:|I )should(?P<negotiation>(\s| not ))see (?P<path>[\/\w\s]+) in main menu$/
     */
    public function iShouldSeeOrNotInMainMenu($negotiation, $path)
    {
        $isMenuItemVisibleExpectation = empty(trim($negotiation));
        /** @var FrontendMainMenu $mainMenu */
        $mainMenu = $this->createElement('FrontendMainMenu');
        $hasLink = $mainMenu->hasLink($path);

        self::assertSame($isMenuItemVisibleExpectation, $hasLink);
    }
}
