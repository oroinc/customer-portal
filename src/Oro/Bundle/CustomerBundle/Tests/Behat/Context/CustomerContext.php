<?php

namespace Oro\Bundle\CustomerBundle\Tests\Behat\Context;

use Oro\Bundle\FormBundle\Tests\Behat\Element\Select2Entity;
use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\OroPageObjectAware;
use Oro\Bundle\TestFrameworkBundle\Tests\Behat\Context\PageObjectDictionary;

class CustomerContext extends OroFeatureContext implements OroPageObjectAware
{
    use PageObjectDictionary;

    /**
     * @When I type :text into Parent Customer field to get all suggestions and see :suggestionCount suggestions
     *
     * @param string $text
     * @param int $suggestionCount
     */
    public function iTypeIntoParentCustomerFieldAndLoadAllSuggestionsToSeeSuggestionCount(
        string $text,
        int $suggestionCount
    ): void {
        /** @var Select2Entity $parentCustomer */
        $parentCustomer = $this->createElement('OroForm')->findField('Parent Customer');
        $results = $parentCustomer->getAllSuggestions(
            $this->getSession(),
            $text
        );

        self::assertCount($suggestionCount, $results);
        $parentCustomer->close();
    }
}
