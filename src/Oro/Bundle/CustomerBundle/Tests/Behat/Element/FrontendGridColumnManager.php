<?php

namespace Oro\Bundle\CustomerBundle\Tests\Behat\Element;

use Oro\Bundle\DataGridBundle\Tests\Behat\Element\GridColumnManager;

class FrontendGridColumnManager extends GridColumnManager
{
    protected function ensureManagerVisible()
    {
        if ($this->isVisible()) {
            return;
        }

        $button = $this->elementFactory->createElement('FrontendGridColumnManagerButton');
        $button->click();

        self::assertTrue($this->isVisible(), 'Can not open grid column manager dropdown');
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    protected function getVisibilityCheckbox($title)
    {
        $field = $this->find('css', '.custom-checkbox__text:contains("' . $title . '")');

        self::assertNotNull($field, 'Can not find visibility cell for ' . $title);

        $input = $field->getParent()->find('css', 'input.custom-checkbox__input');

        self::assertNotNull($input, 'Can not find visibility checkbox for ' . $title);

        return $input;
    }
}
