<?php

namespace Oro\Bundle\CustomerBundle\Tests\Behat\Page;

use Behat\Behat\Tester\Exception\PendingException;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\Page;

class CustomerUserForgotPassword extends Page
{
    #[\Override]
    public function open(array $parameters = [])
    {
        throw new PendingException('Not implemented');
    }
}
