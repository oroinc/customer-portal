<?php

namespace Oro\Bundle\CustomerBundle\Tests\Behat\Context;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;
use Oro\Bundle\CustomerBundle\Tests\Behat\Element\FrontendGridColumnManager;
use Oro\Bundle\DataGridBundle\Tests\Behat\Element\FrontendGridFilterManager;
use Oro\Bundle\DataGridBundle\Tests\Behat\Element\GridInterface;
use Oro\Bundle\FrontendBundle\Tests\Behat\Element\Grid;
use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\Element;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\OroPageObjectAware;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\Table;
use Oro\Bundle\TestFrameworkBundle\Tests\Behat\Context\PageObjectDictionary;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
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
        $grid->getElement('FrontendGridViewName')->setValue($name);
    }

    /**
     * Example: I mark Set as Default on grid view for "TestGrid" grid on frontend
     *
     * @Given /^(?:|I )mark Set as Default on grid view for "(?P<gridName>([\w\s]+))" grid on frontend$/
     *
     * @param string $gridName
     */
    public function markGridViewAsDefault($gridName)
    {
        $grid = $this->getFrontendGrid($gridName);
        $grid->getElement('FrontendGridViewSetAsDefaultCheckbox')->click();
    }

    /**
     * Example: I delete "CustomView" grid view in "TestGrid" frontend grid
     *
     * @Given /^(?:I )?delete "(?P<gridViewName>([^"]+))" grid view in "(?P<gridName>([^"]+))" frontend grid$/
     * @Given /^(?:I )?delete "(?P<gridViewName>([^"]+))" grid view in frontend grid$/
     *
     * @param string $gridViewName
     * @param string $gridName
     */
    public function deleteGridView(string $gridViewName, ?string $gridName = null): void
    {
        $grid = $this->getFrontendGrid($gridName);
        $grid->openGridViewDropdown();

        $gridViewItemElement = $this->getGridViewItem($gridViewName, $gridName);

        $gridViewItemElement->getElement('FrontendGridViewDeleteButton')->click();
    }

    /**
     * Example: I set "CustomView" grid view as default in "TestGrid" frontend grid
     *
     * @Given /^(?:I )?set "(?P<gridViewName>([^"]+))" grid view as default in "(?P<gridName>([^"]+))" frontend grid$/
     * @Given /^(?:I )?set "(?P<gridViewName>([^"]+))" grid view as default in frontend grid$/
     *
     * @param string $gridViewName
     * @param string $gridName
     */
    public function setGridViewAsDefault(string $gridViewName, ?string $gridName = null): void
    {
        $grid = $this->getFrontendGrid($gridName);
        $grid->openGridViewDropdown();

        $gridViewItemElement = $this->getGridViewItem($gridViewName, $gridName);

        $gridViewItemElement->getElement('FrontendGridViewSetAsDefaultButton')->click();

        $this->waitForAjax();

        $grid->closeGridViewDropdown();
    }

    /**
     * Example: I switch to "gridview1" grid view in frontend grid
     *
     * @Given /^(?:I )?switch to "(?P<gridViewName>([^"]+))" grid view in frontend grid$/
     * @Given /^(?:I )?switch to "(?P<gridViewName>([^"]+))" grid view in "(?P<gridName>([\w\s]+))" frontend grid$/
     */
    public function switchToGridView(string $gridViewName, ?string $gridName = null): void
    {
        $this->getFrontendGrid($gridName)->openGridViewDropdown();

        $gridViewItemElement = $this->getGridViewItem($gridViewName, $gridName);

        $gridViewItemElement->getElement('FrontendGridViewsItemLabel')->click();

        $this->waitForAjax();

        // Gets grid element again to avoid stale element error after gridview is applied.
        $this->getFrontendGrid($gridName)->closeGridViewDropdown();
    }

    private function getGridViewItem(string $gridViewName, ?string $gridName = null): Element
    {
        $grid = $this->getFrontendGrid($gridName);
        $gridViewItemElement = $grid->findElementContains('FrontendGridViewsItem', $gridViewName);
        self::assertTrue($gridViewItemElement->isValid(), 'Grid view item not found');

        return $gridViewItemElement;
    }

    /**
     * @param string|null $datagridName
     * @return Grid|Element
     */
    protected function getFrontendGrid($datagridName = null)
    {
        return $this->elementFactory->createElement($datagridName ?: 'FrontendDatagrid');
    }

    /**
     * @When /^(?:|I )show the following columns in frontend grid:$/
     * @When /^(?:|I )show the following columns in "(?P<datagridName>(?:[^"]|\\")*)" frontend grid:$/
     *
     * @param TableNode $table
     * @param string|null $datagridName
     */
    public function checkColumnsOptionsFrontendDatagrid(TableNode $table, $datagridName = null)
    {
        /** @var FrontendGridColumnManager $columnManager */
        $columnManager = $this->getFrontendGridColumnManager($datagridName);
        $columnManager->open();
        foreach ($table->getColumn(0) as $name) {
            $columnManager->checkColumnVisibility($name);
        }
        $columnManager->close();
    }

    /**
     * @When /^(?:|I )hide the following columns in frontend grid:$/
     * @When /^(?:|I )hide the following columns in "(?P<datagridName>(?:[^"]|\\")*)" frontend grid:$/
     *
     * @param TableNode $table
     * @param string|null $datagridName
     */
    public function uncheckColumnsOptionsFrontendDatagrid(TableNode $table, $datagridName = null)
    {
        /** @var FrontendGridColumnManager $columnManager */
        $columnManager = $this->getFrontendGridColumnManager($datagridName);
        $columnManager->open();
        foreach ($table->getColumn(0) as $name) {
            $columnManager->uncheckColumnVisibility($name);
        }
        $columnManager->close();
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
     * @When /^(?:|I )hide column "(?P<columnName>(?:[^"]|\\")*)" in frontend grid$/
     * @When /^(?:|I )hide column "(?P<columnName>(?:[^"]|\\")*)" in "(?P<datagridName>(?:[^"]|\\")*)" frontend grid$/
     *
     * @param string $columnName
     * @param string|null $datagridName
     */
    public function uncheckColumnOptionFrontendDatagrid($columnName, $datagridName = null)
    {
        /** @var FrontendGridColumnManager $columnManager */
        $columnManager = $this->getFrontendGridColumnManager($datagridName);
        $columnManager->open();
        $columnManager->uncheckColumnVisibility($columnName);
        $columnManager->close();
    }

    /**
     * Hide all columns in frontend grid except mentioned
     *
     * @When /^(?:|I) hide all columns in frontend grid except (?P<exceptions>(?:[^"]|\\")*)$/
     * @When /^(?:|I) hide all columns in "(?P<gridName>[\w\s]+)" frontend grid except (?P<exceptions>(?:[^"]|\\")*)$/
     * @When /^(?:|I) hide all columns in frontend grid$/
     * @When /^(?:|I) hide all columns in "(?P<gridName>[\w\s]+)" frontend grid$/
     */
    public function iHideAllColumnsInFrontendGrid(string $exceptions = '', ?string $gridName = null): void
    {
        $exceptColumns = array_filter(array_map('trim', explode(',', $exceptions)));

        $columnManager = $this->getFrontendGridColumnManager($gridName);
        $columnManager->open();
        $columnManager->hideAllColumns($exceptColumns);
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

    /**
     * @Given /^(?:|I) hide filter "(?P<filter>(?:[^"]|\\")*)" in frontend grid$/
     * @Given /^(?:|I) hide filter "(?P<filter>(?:[^"]|\\")*)" in "(?P<datagridName>[\w\s]+)" frontend grid$/
     */
    public function iHideFilterInFrontendGrid(string $filter, ?string $datagridName = null): void
    {
        /** @var FrontendGridFilterManager $filterManager */
        $filterManager = $this->getFilterManager($datagridName);
        $filterManager->uncheckColumnFilter($filter);
        $filterManager->close();
    }

    /**
     * @Given /^(?:|I) sort frontend grid by "(?P<sorter>(?:[^"]|\\")*)"$/
     * @Given /^(?:|I) sort frontend grid "(?P<datagridName>[\w\s]+)" by "(?P<sorter>(?:[^"]|\\")*)"$/
     */
    public function iSortFrontendGrid(string $sorter, ?string $datagridName = null): void
    {
        $grid = $this->getGrid($datagridName);
        $sorterSelect = $grid->getElement($grid->getMappedChildElementName('Frontend Product Grid Sorter'));

        $optionElements = $sorterSelect->findAll('css', 'option');
        $options = [];
        /** @var NodeElement[] $optionElements */
        foreach ($optionElements as $option) {
            $optionLabel = trim(preg_replace('/\s+/', ' ', $option->getText()));
            $options[$optionLabel] = $option->getValue();
        }

        self::assertArrayHasKey(
            $sorter,
            $options,
            sprintf('Sorter %s was not found. Available options are: %s', $sorter, implode(', ', array_keys($options)))
        );

        $sorterSelect->setValue($options[$sorter]);
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
        $filterButton = $grid->getElement($grid->getMappedChildElementName('FrontendGridFilterManagerButton'));
        $filterButton->click();

        return $grid->getElement($grid->getMappedChildElementName('FrontendGridFilterManager'));
    }

    /**
     * @param string|null $datagridName
     * @param string|null $content
     * @return GridInterface|Table|Element
     */
    private function getGrid(?string $datagridName = null, ?string $content = null)
    {
        if ($datagridName === null) {
            $datagridName = 'Grid';
        }

        if ($content !== null) {
            $grid = $this->elementFactory->findElementContains($datagridName, $content);
        } else {
            $grid = $this->elementFactory->createElement($datagridName);
        }

        self::assertTrue($grid->isIsset(), sprintf('Element "%s" not found on the page', $datagridName));

        return $grid;
    }
}
