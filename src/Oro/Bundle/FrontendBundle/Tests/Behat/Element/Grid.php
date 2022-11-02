<?php

namespace Oro\Bundle\FrontendBundle\Tests\Behat\Element;

use Oro\Bundle\DataGridBundle\Tests\Behat\Element\Grid as BaseGrid;

class Grid extends BaseGrid
{
    const DEFAULT_MAPPINGS = [
        'GridRow' => 'FrontendGridRow',
        'GridRowStrict' => 'FrontendGridRow',
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

        $mappings = static::DEFAULT_MAPPINGS;
        if (isset($mappings[$name])) {
            return $mappings[$name];
        }

        return parent::getMappedChildElementName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function selectPageSize($number)
    {
        $pageSizeElement = $this->elementFactory->createElement('PageSize');
        $pageSizeElement->find('css', '.select2-choice')->click();
        $detachedSelect2Result = $this->elementFactory->createElement('DetachedSelect2Result');
        $detachedSelect2Result->find('css', 'div.select2-result-label:contains("' . $number . '")')->click();
    }

    /**
     * {@inheritdoc}
     */
    public function massCheck($title)
    {
        $massActionHeadCheckboxElementName = $this->getMappedChildElementName('MassActionHeadCheckbox');

        $this->elementFactory->createElement($massActionHeadCheckboxElementName, $this)->clickForce();

        $this->elementFactory->createElement('GridMassCheckMenu')->clickLink($title);
    }

    public function openGridViewDropdown(): void
    {
        $gridViewsDropdown = $this->getElement('FrontendGridViewsDropdown');
        if ($gridViewsDropdown->isVisible()) {
            return;
        }

        $gridViewListElement = $this->getElement($this->getMappedChildElementName('GridViewList'));
        self::assertTrue($gridViewListElement->isValid(), 'Grid view list not found on the page');

        $gridViewListElement->click();
    }

    public function closeGridViewDropdown(): void
    {
        $gridViewsDropdown = $this->getElement('FrontendGridViewsDropdown');
        if (!$gridViewsDropdown->isVisible()) {
            return;
        }

        $gridViewListElement = $this->getElement($this->getMappedChildElementName('GridViewList'));
        self::assertTrue($gridViewListElement->isValid(), 'Grid view list not found on the page');

        $gridViewListElement->click();
    }
}
