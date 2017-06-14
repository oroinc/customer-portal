<?php

namespace Oro\Bundle\FrontendBundle\Tests\Behat\Element;

use Behat\Mink\Element\NodeElement;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\Element;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\Table;

use Oro\Bundle\DataGridBundle\Tests\Behat\Element\GridColumnManager;

class DataGridManager extends GridColumnManager
{
    protected function ensureManagerVisible()
    {
        if ($this->isVisible()) {
            return;
        }

        self::assertTrue($this->isVisible(), 'Can not open grid column manager dropdown');
    }

    /**
     * Hide all columns in grid exception mentioned in exceptions array
     *
     * @param array $exceptions
     */
    public function hideAllColumns(array $exceptions = [])
    {
        $this->ensureManagerVisible();

        $rows = $this->getDataGridManagerRows();
        foreach ($rows as $row) {
            $name = $row->find('css', '.custom-checkbox__text')->getText();

            // Skip exceptions
            if (in_array($name, $exceptions, true)) {
                continue;
            }

            $this->uncheckColumnVisibility($name);
        }
    }

    /**
     * @return NodeElement[]
     */
    protected function getDataGridManagerRows() {
        $rows = $this->findAll('css', '[data-role="column-manager-table-wrapper"] table tbody tr');

        self::assertNotNull($rows, 'Cannot find any table row!');

        return $rows;
    }

    /**
     * @param string $title
     * @return NodeElement|null
     */
    protected function getDataGridManagerRowByContent($title) {
        $row = $this->find('css', sprintf('[data-role="column-manager-table-wrapper"] table tbody tr:contains("%s")', $title));

        self::assertNotNull($row, 'Cannot find a table row with this text!');

        return $row;
    }

    /**
     * @param string $title
     * @return NodeElement|mixed|null
     */
    protected function getVisibilityCheckbox($title)
    {
        $tableRow = $this->getDataGridManagerRowByContent($title);

        $visibilityCheckbox = $tableRow->find('css', 'input[type=checkbox]');

        self::assertNotNull($visibilityCheckbox, 'Can not find visibility cell for ' . $title);

        return $visibilityCheckbox;
    }
}
