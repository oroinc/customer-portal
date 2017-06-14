<?php

namespace Oro\Bundle\CustomerBundle\Tests\Behat\Context;

use Oro\Bundle\DataGridBundle\Tests\Behat\Element\Grid;
use Oro\Bundle\FrontendBundle\Tests\Behat\Element\DataGridManager;
use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\OroPageObjectAware;
use Oro\Bundle\TestFrameworkBundle\Tests\Behat\Context\PageObjectDictionary;

class GridContext extends OroFeatureContext implements OroPageObjectAware
{
    use PageObjectDictionary;

    /**
     * Example: I set "Test" as grid view name for "TestGrid" grid on frontend
     *
     * @Given /^(?:|I )set "(?P<name>([\w\s]+))" as grid view name for "(?P<gridName>([\w\s]+))" grid on frontend$/
     *
     * @param string $name
     * @param string $gridName
     */
    public function setGridViewName($name, $gridName)
    {
        $grid = $this->getGrid($gridName);
        $grid->fillField('frontend-grid-view-name', $name);
    }

    /**
     * Example: I mark Set as Default on grid view for "TestGrid" grid on frontend
     *
     * @Given /^(?:|I )mark Set as Default on grid view for "(?P<gridName>([\w\s]+))" grid on frontend$/
     *
     * @param string $gridName
     */
    public function setGridViewAsDefault($gridName)
    {
        $grid = $this->getGrid($gridName);
        $grid->checkField('is_default');
    }

    /**
     * Hide all columns in grid except mentioned
     *
     * @When /^I hide all columns in "(?P<grid>([\w\s]+))" grid except "(?P<exceptions>(?:[^"]|\\")*)" on frontend$/
     * @When /^I hide all columns in grid except "(?P<exceptions>(?:[^"]|\\")*)" on frontend$/
     */
    public function iHideAllColumnsInGrid($grid = 'Frontend Data Grid', $exceptions = '')
    {
        $exceptions = explode(',', $exceptions);
        $exceptions = array_map('trim', $exceptions);
        $exceptions = array_filter($exceptions);

        $gridManager = $this->getDataGridManager($grid);
        $gridManager->hideAllColumns($exceptions);
        $gridManager->close();
    }

    /**
     * @param string|null $grid
     * @return Grid
     */
    protected function getGrid($grid = 'Frontend Data Grid')
    {
        return $this->elementFactory->createElement($grid);
    }

    /**
     * @return DataGridManager
     */
    private function getDataGridManager($grid = 'Frontend Data Grid')
    {
        $gridManager = $grid . ' ' . 'Manager';
        return $this->createElement($gridManager);
    }
}
