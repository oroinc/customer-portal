<?php

namespace Oro\Bundle\CustomerBundle\Tests\Behat\Element;

use Behat\Mink\Element\NodeElement;
use Oro\Bundle\DataGridBundle\Tests\Behat\Element\GridColumnManager;

class FrontendGridColumnManager extends GridColumnManager
{
    #[\Override]
    public function checkColumnVisibility($title)
    {
        $this->ensureManagerVisible();

        $visibilityCell = $this->getVisibilityCheckbox($title);

        if ($visibilityCell->isChecked()) {
            return;
        }

        $visibilityCell->getParent()->press();

        self::assertTrue($visibilityCell->isChecked(), 'Can not check visibility checkbox for ' . $title . ' column');
    }

    /**
     * @param string $title
     */
    #[\Override]
    public function uncheckColumnVisibility($title)
    {
        $this->ensureManagerVisible();

        $visibilityCheckbox = $this->getVisibilityCheckbox($title);

        if (!$visibilityCheckbox->isChecked()) {
            return;
        }

        $visibilityCheckbox->getParent()->click();

        self::assertFalse(
            $visibilityCheckbox->isChecked(),
            'Can not uncheck visibility checbox for ' . $title . ' column'
        );
    }

    #[\Override]
    protected function ensureManagerVisible()
    {
        if ($this->isVisible()) {
            return;
        }

        if ($this->grid) {
            $button = $this->grid->getElement('FrontendGridColumnManagerButton');
        } else {
            $button = $this->elementFactory->createElement('FrontendGridColumnManagerButton');
        }
        $button->click();

        self::assertTrue($this->isVisible(), 'Can not open grid column manager dropdown');
    }

    #[\Override]
    public function hideAllColumns(array $exceptions = [])
    {
        $this->ensureManagerVisible();

        $rows = $this->getDataGridManagerRows();
        foreach ($rows as $row) {
            $name = $row->find('css', '.checkbox-label')->getText();

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
    protected function getDataGridManagerRows()
    {
        $rows = $this->findAll('css', '[data-role="datagrid-settings-table-wrapper"] table tbody tr');

        self::assertNotNull($rows, 'Cannot find any table row!');

        return $rows;
    }

    #[\Override]
    protected function getVisibilityCheckbox($title)
    {
        $field = $this->find('xpath', '//label[@class="checkbox-label"][contains(.,"' . $title . '")]');

        self::assertNotNull($field, 'Can not find visibility cell for ' . $title);

        $input = $field->find('css', 'input[type=checkbox]');

        self::assertNotNull($input, 'Can not find visibility checkbox for ' . $title);

        return $input;
    }
}
