<?php

namespace Oro\Bundle\FrontendBundle\Tests\Behat\Element;

use Oro\Bundle\DataGridBundle\Tests\Behat\Element\Grid as BaseGrid;

class Grid extends BaseGrid
{
    const DEFAULT_MAPPINGS = [
        'GridRow' => 'FrontendGridRow',
        'GridToolbarPaginator' => 'FrontendGridToolbarPaginator',
        'MassActionHeadCheckbox' => 'FrontendMassActionHeadCheckbox',
        'GridColumnManager' => 'FrontendGridColumnManager',
        'GridFilterManager' => 'FrontendGridFilterManager',
    ];

    /**
     * {@inheritdoc}
     */
    public function getMappedChildElementName($name)
    {
        if (isset($this->options['mapping'][$name])) {
            return $this->options['mapping'][$name];
        }

        $mappings = self::DEFAULT_MAPPINGS;
        if (isset($mappings[$name])) {
            return $mappings[$name];
        }

        return parent::getMappedChildElementName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function massCheck($title)
    {
        $massActionHeadCheckboxElementName = $this->getMappedChildElementName('MassActionHeadCheckbox');

        $this->elementFactory->createElement($massActionHeadCheckboxElementName, $this)->clickForce();

        $this->elementFactory->createElement('GridFloatingMenu')->clickLink($title);
    }
}
