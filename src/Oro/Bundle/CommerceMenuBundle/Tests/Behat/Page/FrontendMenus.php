<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Behat\Page;

use Oro\Bundle\TestFrameworkBundle\Behat\Element\Page;

class FrontendMenus extends Page
{
    #[\Override]
    public function open(array $parameters = [])
    {
        $this->getMainMenu()->openAndClick('System/Storefront Menus');
    }
}
