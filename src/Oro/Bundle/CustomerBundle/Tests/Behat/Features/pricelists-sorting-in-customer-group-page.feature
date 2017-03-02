@fixture-BuyerCustomerFixture.yml
Feature: Price lists must be sortable in customerGroup create\view page

  Scenario: Changing Price List Priorities In Customers
    Given I login as administrator
    And I go to Customers/Customers
    And I click Edit first customer in grid
    Then I should not see "Priority" in price lists table
    And I should see drag-n-drop icon present on price list line
    When I click "Add Price List"
    And I choose a price list "first price list" in "2" row
    And I choose a price list "second price list" in "1" row
    And I drag "2" row to the top in price lists table
    And I click "Save and Close"
    Then I should see "Customer has been saved" flash message
    And first price list must be first record in appropriate table
