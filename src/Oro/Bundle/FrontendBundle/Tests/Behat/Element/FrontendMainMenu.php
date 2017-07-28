<?php

namespace Oro\Bundle\FrontendBundle\Tests\Behat\Element;

use Behat\Mink\Element\NodeElement;
use Oro\Bundle\NavigationBundle\Tests\Behat\Element\MainMenu;

class FrontendMainMenu extends MainMenu
{
    /**
     * @param NodeElement $link
     */
    protected function getDropDown($link)
    {
        $this->dropDown = $this->elementFactory->wrapElement(
            'FrontendMainMenuDropdown',
            $link->getParent()->find('css', '.main-menu__item--ancestor')
        );
    }
}
