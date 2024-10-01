<?php

namespace Oro\Bundle\CustomerBundle\Tests\Behat\Page;

use Oro\Bundle\TestFrameworkBundle\Behat\Element\Page;

class CustomerUserProfile extends Page
{
    #[\Override]
    public function open(array $parameters = [])
    {
        $page = $this->elementFactory->getPage();
        $page->clickLink('Account');
        $page->clickOrPress('My Profile');
    }
}
