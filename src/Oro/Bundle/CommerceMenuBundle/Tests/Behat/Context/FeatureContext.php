<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Behat\Context;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Oro\Bundle\DataGridBundle\Tests\Behat\Context\GridContext;
use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\OroPageObjectAware;
use Oro\Bundle\TestFrameworkBundle\Tests\Behat\Context\OroMainContext;
use Oro\Bundle\TestFrameworkBundle\Tests\Behat\Context\PageObjectDictionary;

class FeatureContext extends OroFeatureContext implements OroPageObjectAware
{
    use PageObjectDictionary;

    private ?OroMainContext $oroMainContext = null;
    private ?GridContext $gridContext = null;

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope): void
    {
        $environment = $scope->getEnvironment();
        $this->oroMainContext = $environment->getContext(OroMainContext::class);
        $this->gridContext = $environment->getContext(GridContext::class);
    }

    /**
     * @Given /^I set "(?P<webCatalogName>[\w\s]+)" root node as main menu navigation root$/
     */
    public function iSetWebCatalogRootNodeAsMenuNavigationRoot(string $webCatalogName): void
    {
        $this->oroMainContext->iOpenTheMenuAndClick('System/Frontend Menus');
        $this->waitForAjax();

        $this->gridContext->clickActionInRow('commerce_main_menu', 'View');
        $this->waitForAjax();

        $this->oroMainContext->selectOption('Target Type', 'Content Node');
        $this->waitForAjax();

        $this->oroMainContext->fillField('Web Catalog', $webCatalogName);
        $this->waitForAjax();

        // Clicks on the first node in tree.
        $this->oroMainContext->iClickOnNodeInTree('', 'Menu Update Content Node Field');
        $this->waitForAjax();

        $this->oroMainContext->fillField('Max Traverse Level', 6);
        $this->waitForAjax();

        $this->oroMainContext->pressButton('Save');
    }

    /**
     * @Given /^I set master catalog root category as main menu navigation root$/
     */
    public function iSetMasterCatalogRootCategoryAsMenuNavigationRoot(): void
    {
        $this->oroMainContext->iOpenTheMenuAndClick('System/Frontend Menus');
        $this->waitForAjax();

        $this->gridContext->clickActionInRow('commerce_main_menu', 'View');
        $this->waitForAjax();

        $this->oroMainContext->selectOption('Target Type', 'Category');
        $this->waitForAjax();

        // Clicks on the first node in tree.
        $this->oroMainContext->iClickOnNodeInTree('', 'Menu Update Category Field');
        $this->waitForAjax();

        $this->oroMainContext->fillField('Max Traverse Level', 6);
        $this->waitForAjax();

        $this->oroMainContext->pressButton('Save');
    }
}
