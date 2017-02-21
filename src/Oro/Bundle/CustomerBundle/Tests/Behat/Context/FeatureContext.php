<?php

namespace Oro\Bundle\CustomerBundle\Tests\Behat\Context;

use Behat\Mink\Element\NodeElement;
use Doctrine\Common\Persistence\ObjectRepository;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\PricingBundle\Tests\Behat\Context\FeatureContext as BaseFeatureContext;
use Oro\Bundle\UserBundle\Entity\Role;

/**
 * TODO: get rid of inheritance after BAP-13903 is done
 */
class FeatureContext extends BaseFeatureContext
{
    /**
     * @Then I should see that :priceListName price list is in :rowNum row on view page
     * @param string $priceListName
     * @param int $rowNum
     */
    public function assertPriceListNameInRow($priceListName, $rowNum)
    {
        --$rowNum;
        $page = $this->getPage();
        $elem = $page->find('named', ['content', $priceListName]);
        self::assertEquals('td', $elem->getTagName());
        $table = $elem->getParent()->getParent();
        self::assertEquals('tbody', $table->getTagName());
        $rows = $table->findAll('css', 'tr');
        self::assertNotEmpty($rows[$rowNum]);
        self::assertContains($priceListName, $rows[$rowNum]->getText());
    }
}
