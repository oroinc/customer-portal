<?php

namespace Oro\Bundle\FrontendBundle\Tests\Behat\Element;

use Oro\Bundle\DataGridBundle\Tests\Behat\Element\GridPaginatorInterface;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\Element;

class GridToolbarPaginator extends Element implements GridPaginatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function getTotalRecordsCount()
    {
        preg_match('/(?P<count>\d+)\s+(Total)/i', $this->getText(), $matches);

        return isset($matches['count']) ? (int) $matches['count'] : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalPageCount()
    {
        return 1;
    }
}
