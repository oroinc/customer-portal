<?php

namespace Oro\Bundle\FrontendBundle\Tests\Behat\Element;

use Behat\Mink\Element\NodeElement;
use Oro\Bundle\NavigationBundle\Tests\Behat\Element\MainMenu;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\Element;

class FrontendMainMenu extends MainMenu
{
    /**
     * {@inheritDoc}
     */
    protected function getDropDown(NodeElement $link): Element
    {
        return $this->elementFactory->wrapElement(
            'FrontendMainMenuDropdown',
            $link->getParent()->getParent()->find('css', '[data-main-menu-item]')
        );
    }
}
