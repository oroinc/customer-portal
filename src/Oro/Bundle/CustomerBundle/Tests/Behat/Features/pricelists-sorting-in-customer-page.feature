@fixture-BuyerCustomerFixture.yml
Feature: Price lists must be sortable in customer & customerGroup create\edit page

  Scenario: Customer create\edit page should contain sortable "price lists"
    Given I login as administrator
    And I go to "/admin/customer/view/1"
    And I click "Edit"
    Then I should see "Price Lists"
    And I should see "Priority"
    When I add price list "first price list" into price lists collection
    And I set priority "400" to price list "first price list"
    And I click "Save and Close"
    Then I should see "Customer has been saved" flash message

  Scenario: Customer group create\edit page should contain sortable "price lists"
    Given I login as administrator
    And I go to "/admin/customer/group/view/1"
    And I click "Edit"
    Then I should see "Price Lists"
    And I should see "Priority"
    When I add price list "first price list" into price lists collection
    And I set priority "400" to price list "first price list"
    And I click "Save and Close"
    Then I should see "Customer group has been saved" flash message
