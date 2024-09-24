<?php

namespace Oro\Bundle\FrontendBundle\Tests\Behat\Element;

use Oro\Bundle\DataGridBundle\Tests\Behat\Element\AbstractGridFilterItem;

class GridFilterItem extends AbstractGridFilterItem
{
    /**
     * Apply filter to the grid
     */
    #[\Override]
    public function submit()
    {
        $this->find('css', '.filter-update')->click();
    }

    #[\Override]
    public function reset()
    {
        $this->find('css', 'button.reset-filter')->click();
        $this->getDriver()->waitForAjax();
    }
}
