<?php

namespace Oro\Bundle\CustomerBundle\Tests\Behat\Page;

use Oro\Bundle\TestFrameworkBundle\Behat\Element\Page;

class CustomerUserIndex extends Page
{
    #[\Override]
    public function open(array $parameters = [])
    {
        $this->getMainMenu()->openAndClick('Customers/Customer Users');
    }
}
