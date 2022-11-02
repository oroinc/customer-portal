<?php

namespace Oro\Bundle\CustomerBundle\Tests\Behat\Page;

use Oro\Bundle\TestFrameworkBundle\Behat\Element\Page;

class CustomerUserProfileUpdate extends Page
{
    /**
     * {@inheritdoc}
     */
    public function open(array $parameters = [])
    {
        $page = $this->elementFactory->getPage();
        $page->clickLink('Account');
        $page->clickOrPress('My Profile');

        $button = $this->elementFactory->createElement('Edit Profile Button');
        $button->click();
    }
}
