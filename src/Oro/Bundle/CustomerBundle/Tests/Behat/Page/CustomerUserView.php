<?php

namespace Oro\Bundle\CustomerBundle\Tests\Behat\Page;

use Oro\Bundle\DataGridBundle\Tests\Behat\Element\Grid;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\Page;

class CustomerUserView extends Page
{
    /**
     * {@inheritdoc}
     */
    public function open(array $parameters = [])
    {
        $this->getMainMenu()->openAndClick('Customers/Customer Users');
        $this->waitForAjax();

        /** @var Grid $grid */
        $grid = $this->elementFactory->createElement('Grid');
        $grid->clickActionLink($parameters['title'], 'View');
    }
}
