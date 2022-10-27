<?php

namespace Oro\Bundle\FrontendBundle\Tests\Behat\Element;

use Oro\Bundle\DataGridBundle\Tests\Behat\Element\GridRow as BaseGridRow;

class GridRow extends BaseGridRow
{
    /**
     * @param int $cellNumber
     */
    public function checkMassActionCheckbox($cellNumber = 0)
    {
        $rowCheckbox = $this->getCellByNumber($cellNumber)->find('css', '[type="checkbox"]');
        self::assertNotNull($rowCheckbox, sprintf('No mass action checkbox found for "%s"', $this->getText()));

        if ($rowCheckbox->isChecked()) {
            return;
        }

        $rowCheckbox->click();
    }

    /**
     * @param int $cellNumber
     */
    public function uncheckMassActionCheckbox($cellNumber = 0)
    {
        $rowCheckbox = $this->getCellByNumber($cellNumber)->find('css', '[type="checkbox"]');
        self::assertNotNull($rowCheckbox, sprintf('No mass action checkbox found for "%s"', $this->getText()));

        if (!$rowCheckbox->isChecked()) {
            return;
        }

        $rowCheckbox->click();
    }
}
