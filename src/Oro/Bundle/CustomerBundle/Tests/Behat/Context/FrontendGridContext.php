<?php

namespace Oro\Bundle\CustomerBundle\Tests\Behat\Context;

use Oro\Bundle\CustomerBundle\Tests\Behat\Element\FrontendGridColumnManager;
use Oro\Bundle\DataGridBundle\Tests\Behat\Element\FrontendGridFilterManager;
use Oro\Bundle\DataGridBundle\Tests\Behat\Element\Grid;
use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\Element;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\OroPageObjectAware;
use Oro\Bundle\TestFrameworkBundle\Tests\Behat\Context\PageObjectDictionary;

class FrontendGridContext extends OroFeatureContext implements OroPageObjectAware
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
        $grid = $this->getFrontendGrid($gridName);
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
        $grid = $this->getFrontendGrid($gridName);
        $grid->getElement('FrontendGridViewSetAsDefaultCheckbox')->click();
    }

    /**
     * @param string|null $datagridName
     * @return Grid|Element
     */
    protected function getFrontendGrid($datagridName = null)
    {
        return $this->elementFactory->createElement($datagridName ?: 'FrontendDatagrid');
    }

    //@codingStandardsIgnoreStart
    /**
     * @When /^(?:|I )show column "(?P<columnName>(?:[^"]|\\")*)" in frontend grid$/
     * @When /^(?:|I )show column "(?P<columnName>(?:[^"]|\\")*)" in "(?P<datagridName>(?:[^"]|\\")*)" frontend grid$/
     *
     * @param string $columnName
     * @param string|null $datagridName
     */
    //@codingStandardsIgnoreEnd
    public function checkColumnOptionFrontendDatagrid($columnName, $datagridName = null)
    {
        /** @var FrontendGridColumnManager $columnManager */
        $columnManager = $this->getFrontendGridColumnManager($datagridName);
        $columnManager->open();
        $columnManager->checkColumnVisibility($columnName);
        $columnManager->close();
    }

    /**
     * @When /^(?:|I )go to next page in grid$/
     * @When /^(?:|I )go to next page in "(?P<datagridName>[\w\s]+)"$/
     *
     * @param null|string $datagridName
     */
    public function iGoToNextPageInGrid($datagridName = null)
    {
        $grid = $this->getFrontendGrid($datagridName);

        $grid->getElement('FrontendGridNextPageButton')->click();
    }

    /**
     * @When /^(?:|I )go to prev page in grid$/
     * @When /^(?:|I )go to prev page in "(?P<datagridName>[\w\s]+)"$/
     *
     * @param null|string $datagridName
     */
    public function iGoToPrevPageInGrid($datagridName = null)
    {
        $grid = $this->getFrontendGrid($datagridName);

        $grid->getElement('FrontendGridPrevPageButton')->click();
    }

    /**
     * @Then /^(?:|I )shouldn't see "(?P<filter>(?:[^"]|\\")*)" filter in frontend grid$/
     * @Then /^(?:|I )shouldn't see "(?P<filter>(?:[^"]|\\")*)" filter in "(?P<datagridName>[\w\s]+)" frontend grid$/
     *
     * @param string $filter
     * @param null|string $datagridName
     */
    public function iShouldNotSeeFilterInFrontendGrid($filter, $datagridName = null)
    {
        /** @var FrontendGridFilterManager $filterManager */
        $filterManager = $this->getFilterManager($datagridName);
        self::assertFalse($filterManager->isCheckColumnFilter($filter));
        $filterManager->close();
    }

    /**
     * @Then /^(?:|I )should see "(?P<filter>(?:[^"]|\\")*)" filter in frontend grid$/
     * @Then /^(?:|I )should see "(?P<filter>(?:[^"]|\\")*)" filter in "(?P<datagridName>[\w\s]+)" frontend grid$/
     *
     * @param string $filter
     * @param null|string $datagridName
     */
    public function iShouldSeeFilterInFrontendGrid($filter, $datagridName = null)
    {
        /** @var FrontendGridFilterManager $filterManager */
        $filterManager = $this->getFilterManager($datagridName);
        self::assertTrue($filterManager->isCheckColumnFilter($filter));
        $filterManager->close();
    }

    /**
     * @codingStandardsIgnoreStart
     *
     * @Then /^(?:|I )should see available "(?P<filter>(?:[^"]|\\")*)" filter in frontend grid$/
     * @Then /^(?:|I )should see available "(?P<filter>(?:[^"]|\\")*)" filter in "(?P<datagridName>[\w\s]+)" frontend grid$/
     *
     * @codingStandardsIgnoreEnd
     *
     * @param string $filter
     * @param null|string $dataGridName
     */
    public function assertHasFilterInManagerInFrontendGrid(string $filter, string $dataGridName = null)
    {
        /** @var FrontendGridFilterManager $filterManager */
        $filterManager = $this->getFilterManager($dataGridName);
        self::assertTrue(
            $filterManager->hasFilter($filter),
            sprintf('Filter %s is not present in %s grid filter manager', $filter, $dataGridName)
        );
        $filterManager->close();
    }

    /**
     * @codingStandardsIgnoreStart
     *
     * @Then /^(?:|I )should see no available "(?P<filter>(?:[^"]|\\")*)" filter in frontend grid$/
     * @Then /^(?:|I )should see no available "(?P<filter>(?:[^"]|\\")*)" filter in "(?P<datagridName>[\w\s]+)" frontend grid$/
     *
     * @codingStandardsIgnoreEnd
     *
     * @param string $filter
     * @param null|string $dataGridName
     */
    public function assertHasNoFilterInManagerInFrontendGrid(string $filter, string $dataGridName = null)
    {
        /** @var FrontendGridFilterManager $filterManager */
        $filterManager = $this->getFilterManager($dataGridName);
        self::assertFalse(
            $filterManager->hasFilter($filter),
            sprintf('Filter %s is present in %s grid filter manager', $filter, $dataGridName)
        );
        $filterManager->close();
    }

    /**
     * @Given /^(?:|I) show filter "(?P<filter>(?:[^"]|\\")*)" in frontend grid$/
     * @Given /^(?:|I) show filter "(?P<filter>(?:[^"]|\\")*)" in "(?P<datagridName>[\w\s]+)" frontend grid$/
     *
     * @param string $filter
     * @param string $datagridName
     */
    public function iShowFilterInFrontendGrid($filter, $datagridName = null)
    {
        /** @var FrontendGridFilterManager $filterManager */
        $filterManager = $this->getFilterManager($datagridName);
        $filterManager->checkColumnFilter($filter);
        $filterManager->close();
    }

    //@codingStandardsIgnoreStart
    /**
     * @Then /^(?:|I )shouldn't see "(?P<columnName>(?:[^"]|\\")*)" column in frontend grid$/
     * @Then /^(?:|I )shouldn't see "(?P<columnName>(?:[^"]|\\")*)" column in "(?P<datagridName>[\w\s]+)" frontend grid$/
     *
     * @param string $columnName
     * @param null|string $datagridName
     */
    //@codingStandardsIgnoreEnd
    public function iShouldNotSeeColumnInGrid($columnName, $datagridName = null)
    {
        self::assertFalse(
            $this->getFrontendGrid($datagridName)->getHeader()->hasColumn($columnName),
            sprintf('"%s" column is in grid', $columnName)
        );
    }

    /**
     * @Then /^(?:|I )should see "(?P<columnName>(?:[^"]|\\")*)" column in frontend grid$/
     * @Then /^(?:|I )should see "(?P<columnName>(?:[^"]|\\")*)" column in "(?P<datagridName>[\w\s]+)" frontend grid$/
     *
     * @param string $columnName
     * @param null|string $datagridName
     */
    public function iShouldSeeColumnInGrid($columnName, $datagridName = null)
    {
        self::assertTrue(
            $this->getFrontendGrid($datagridName)->getHeader()->hasColumn($columnName),
            sprintf('"%s" column is not in grid', $columnName)
        );
    }

    /**
     * @param string $datagridName
     *
     * @return Element|FrontendGridColumnManager
     */
    private function getFrontendGridColumnManager($datagridName = null)
    {
        $grid = $this->getFrontendGrid($datagridName);
        $manager = $grid->getElement('FrontendGridColumnManager');
        self::assertNotNull($manager);
        return $manager;
    }

    /**
     * @param null $datagridName
     *
     * @return FrontendGridFilterManager|Element
     */
    protected function getFilterManager($datagridName = null)
    {
        $grid = $this->getFrontendGrid($datagridName);

        $grid->getElement($grid->getMappedChildElementName('GridFiltersButton'))->open();
        $filterButton = $grid->getElement($grid->getMappedChildElementName('GridFilterManagerButton'));
        $filterButton->click();

        return $grid->getElement($grid->getMappedChildElementName('FrontendGridFilterManager'));
    }
}
