<?php

namespace Oro\Bundle\FrontendBundle\Tests\Behat\Element;

use Oro\Bundle\DataGridBundle\Tests\Behat\Element\GridPaginatorInterface;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\Element;

class GridToolbarPaginator extends Element implements GridPaginatorInterface
{
    #[\Override]
    public function getTotalRecordsCount()
    {
        preg_match('/(?:\d+\s\w+\s)?(?P<count>\d+)\s+([\w\s]+)/i', $this->getText(), $matches);

        return isset($matches['count']) ? (int) $matches['count'] : 0;
    }

    #[\Override]
    public function getTotalPageCount()
    {
        return 1;
    }
}
