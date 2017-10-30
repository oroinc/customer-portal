<?php

namespace Oro\Bundle\FrontendBundle\Tests\Behat\Element;

use Behat\Mink\Element\NodeElement;
use Oro\Bundle\DataGridBundle\Tests\Behat\Element\GridRow as BaseGridRow;

class GridRow extends BaseGridRow
{
    /**
     * @param int $number Row index number starting from 0
     * @return NodeElement
     */
    public function getCellByNumber($number)
    {
        switch ($number) {
            case 0:
                return $this->find('css', 'div.product-item__image-holder');

            case 1:
                return $this->find('css', 'div.product-item__primary-content');

            case 2:
                return $this->find('css', 'div.product-item__secondary-content');

            default:
                throw new \LogicException(sprintf('Unknown column number %d', $number));
        }
    }

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

        $rowCheckbox->find('xpath', '../span[@class="custom-checkbox__icon"]')->click();
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

        $rowCheckbox->find('xpath', '../span[@class="custom-checkbox__icon"]')->click();
    }
}
