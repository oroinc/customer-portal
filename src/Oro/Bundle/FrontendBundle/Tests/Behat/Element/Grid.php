<?php

namespace Oro\Bundle\FrontendBundle\Tests\Behat\Element;

use Behat\Mink\Element\NodeElement;
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

    public function massVisibleOnPageCheck()
    {
        $this->elementFactory->createElement('GridMassCheckboxLabel')->click();
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

    /**
     * {@inheritdoc}
     */
    public function hasMassActionLink($title): bool
    {
        return (bool)$this->getMassActionLink($title)?->isVisible();
    }

    /**
     * {@inheritdoc}
     */
    public function getMassActionLink($title): ?NodeElement
    {
        return $this->elementFactory->createElement($this->getMappedChildElementName('Toolbar Mass Actions'), $this)
            ->findLink($title);
    }

    /**
     * {@inheritdoc}
     */
    public function clickMassActionLink($title)
    {
        $massActionLink = $this->getMassActionLink($title);
        self::assertNotNull($massActionLink, 'Mass action link not found on the page');
        self::assertTrue($massActionLink->isVisible(), 'Mass action link is not visible');

        $massActionLink->click();
    }

    /**
     * {@inheritdoc}
     */
    public function clickSelectAllMassActionLink($title)
    {
        $massActionLink = $this->getMassActionLink($title);
        self::assertNotNull($massActionLink, 'Mass action link not found on the page');
        self::assertTrue($massActionLink->isVisible(), 'Mass action link is not visible');

        $massActionLink->click();
    }
}
