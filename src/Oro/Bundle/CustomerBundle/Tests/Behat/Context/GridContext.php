<?php

namespace Oro\Bundle\CustomerBundle\Tests\Behat\Context;

use Oro\Bundle\FrontendBundle\Tests\Behat\Element\FrontendGridFilterManager;
use Oro\Bundle\CustomerBundle\Tests\Behat\Element\FrontendGridColumnManager;
use Oro\Bundle\DataGridBundle\Tests\Behat\Element\Grid;
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
     * @param string|null $grid
     * @return Grid
     */
    protected function getGrid($grid = 'Grid')
    {
        return $this->elementFactory->createElement($grid);
    }

    //@codingStandardsIgnoreStart
    /**
     * @When /^(?:|I )show column "(?P<columnName>(?:[^"]|\\")*)" in "(?P<datagridName>(?:[^"]|\\")*)" frontend grid$/
     * @When /^(?:|I )mark as visible column "(?P<columnName>(?:[^"]|\\")*)" in "(?P<datagridName>(?:[^"]|\\")*)" frontend grid$/
     *
     * @param string $columnName
     * @param string $datagridName
     */
    //@codingStandardsIgnoreEnd
    public function checkColumnOptionFrontendDatagrid($columnName, $datagridName)
    {
        $grid = $this->getGrid($datagridName);

        /** @var FrontendGridColumnManager $columnManager */
        $columnManager = $grid->getElement('FrontendGridColumnManager');
        $columnManager->open();
        $columnManager->checkColumnVisibility($columnName);
        $columnManager->close();
    }
}
