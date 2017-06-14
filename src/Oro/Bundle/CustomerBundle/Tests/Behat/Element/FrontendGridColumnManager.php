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
    protected function getVisibilityCheckbox($title)
    {
        $field = $this->find('css', '.custom-checkbox__text:contains("' . $title . '")');

        self::assertNotNull($field, 'Can not find visibility cell for ' . $title);

        $input = $field->getParent()->find('css', 'input.custom-checkbox__input');

        self::assertNotNull($input, 'Can not find visibility checkbox for ' . $title);

        return $input;
    }
}
